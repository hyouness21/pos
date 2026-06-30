<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceService;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Invoice::with('customer')->latest();

        if ($request->filled('search')) {
            $query->where('id', $request->input('search'));
        }

        if ($request->filled('customer')) {
            $query->whereHas('customer', fn($q) => $q->where('name', 'like', '%' . $request->customer . '%'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
        }

        $summary = (clone $query)->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount),0) as total, COALESCE(SUM(CASE WHEN status="paid" THEN total_amount END),0) as paid, COALESCE(SUM(CASE WHEN status="pending" THEN total_amount END),0) as pending')->first();

        $invoices = $query->paginate(20)->withQueryString();

        return view('invoices.index', compact('invoices', 'summary'));
    }

    public function create(): View
    {
        $customers  = Customer::orderBy('name')->get();
        $categories = Category::with('items')->get();

        return view('invoices.create', compact('customers', 'categories'));
    }

    public function store(Request $request, InvoiceService $service): RedirectResponse
    {
        $request->validate([
            'customer_id'          => 'required_without:new_customer_name|nullable|exists:customers,id',
            'new_customer_name'    => 'required_without:customer_id|nullable|string|max:255',
            'new_customer_phone'   => 'required_with:new_customer_name|nullable|string|max:50',
            'new_customer_address' => 'nullable|string|max:500',
            'payment_method'       => 'required|in:cash,pay_later',
            'notes'                => 'nullable|string',
            'lines'                => 'required|array|min:1',
            'lines.*.item_id'      => 'required|exists:items,id',
            'lines.*.quantity'     => 'required|integer|min:1',
            'lines.*.unit_price'   => 'required|numeric|min:0',
            'discount'             => 'nullable|numeric|min:0',
            'invoice_date'         => 'nullable|date|before_or_equal:today',
        ]);

        if ($request->filled('new_customer_name')) {
            $existing = Customer::where('phone', $request->new_customer_phone)->first();
            if ($existing) {
                return back()
                    ->withInput()
                    ->withErrors(['new_customer_phone' => 'A customer with this phone number already exists: ' . $existing->name . '. Please select them from the list.']);
            }
            $customer = Customer::create([
                'name'    => $request->new_customer_name,
                'phone'   => $request->new_customer_phone,
                'address' => $request->new_customer_address ?: null,
            ]);
            $request->merge(['customer_id' => $customer->id]);
        }

        $invoice = $service->create($request->only(['customer_id', 'payment_method', 'notes', 'discount']), $request->lines, $request->invoice_date);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created.');
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load('items.item');

        $stockAdjustments = $invoice->items
            ->groupBy('item_id')
            ->map(fn ($items) => $items->sum('quantity'));

        $categories = Category::with('items')->get();
        $categories->each(function ($cat) use ($stockAdjustments) {
            $cat->items->each(function ($item) use ($stockAdjustments) {
                $item->stock += $stockAdjustments->get($item->id, 0);
            });
        });

        $existingLines = $invoice->items
            ->groupBy('item_id')
            ->map(function ($items) {
                $paid      = $items->where('unit_price', '>', 0);
                $free      = $items->where('unit_price', '=', 0);
                $itemModel = $items->first()->item;
                $unitPrice = $paid->first()?->unit_price ?? $itemModel->price;

                return [
                    'item_id'        => (int) $itemModel->id,
                    'name'           => $itemModel->name,
                    'original_price' => (float) $unitPrice,
                    'unit_price'     => (float) $unitPrice,
                    'stock'          => (int) $itemModel->stock,
                    'quantity'       => (int) ($paid->sum('quantity') + $free->sum('quantity')),
                    'free_qty'       => (int) $free->sum('quantity'),
                ];
            })->values();

        return view('invoices.edit', compact('invoice', 'categories', 'existingLines'));
    }

    public function update(Request $request, Invoice $invoice, InvoiceService $service): RedirectResponse
    {
        $request->validate([
            'payment_method'     => 'required|in:cash,pay_later',
            'notes'              => 'nullable|string',
            'lines'              => 'required|array|min:1',
            'lines.*.item_id'    => 'required|exists:items,id',
            'lines.*.quantity'   => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'discount'           => 'nullable|numeric|min:0',
        ]);

        $service->update($invoice, $request->only(['payment_method', 'notes', 'discount']), $request->lines);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load('customer', 'items.item');
        return view('invoices.show', compact('invoice'));
    }

    public function pdf(Invoice $invoice): \Illuminate\Http\Response
    {
        $invoice->load('customer', 'items.item');

        $html = view('invoices.pdf', compact('invoice'))->render();

        if (app()->getLocale() === 'ar') {
            $arabic = new Arabic();
            $html = $arabic->utf8Glyphs($html);
            // Restore Western numerals — ArPHP converts them to Arabic-Eastern
            $html = str_replace(
                ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
                ['0','1','2','3','4','5','6','7','8','9'],
                $html
            );
        }

        $pdf = Pdf::loadHTML($html)->setPaper('a5', 'portrait');
        $filename = "invoice-{$invoice->id}.pdf";

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->load('items');

        foreach ($invoice->items as $line) {
            \App\Models\Item::where('id', $line->item_id)
                ->increment('stock', $line->quantity);
        }

        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice deleted.');
    }

    public function updateStatus(Request $request, Invoice $invoice, InvoiceService $service): RedirectResponse
    {
        $request->validate(['status' => 'required|in:paid,pending']);

        if ($request->status === 'paid') {
            $service->markPaid($invoice);
        } else {
            $service->markPending($invoice);
        }

        return back()->with('success', 'Invoice status updated.');
    }

    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $remaining = $invoice->remaining();

        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $remaining],
        ]);

        $newPaid  = (float) $invoice->amount_paid + (float) $request->amount;
        $newPaid  = min($newPaid, (float) $invoice->total_amount);
        $status   = $newPaid >= (float) $invoice->total_amount ? 'paid' : 'pending';

        $invoice->update(['amount_paid' => $newPaid, 'status' => $status]);

        return back()->with('success', 'Payment recorded.');
    }
}
