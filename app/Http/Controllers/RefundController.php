<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Refund;
use App\Models\RefundItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    public function create(Invoice $invoice)
    {
        $invoice->load(['items.item', 'customer', 'refunds.items']);

        // Calculate already-refunded quantities per invoice item
        $refundedQty = [];
        foreach ($invoice->refunds as $refund) {
            foreach ($refund->items as $ri) {
                $refundedQty[$ri->item_id] = ($refundedQty[$ri->item_id] ?? 0) + $ri->quantity;
            }
        }

        $lines = $invoice->items->map(function ($line) use ($refundedQty) {
            $alreadyRefunded = $refundedQty[$line->item_id] ?? 0;
            return [
                'invoice_item_id' => $line->id,
                'item_id'         => $line->item_id,
                'name'            => $line->item->name,
                'unit_price'      => (float) $line->unit_price,
                'sold_qty'        => $line->quantity,
                'refunded_qty'    => $alreadyRefunded,
                'refundable_qty'  => max(0, $line->quantity - $alreadyRefunded),
            ];
        })->filter(fn ($l) => $l['refundable_qty'] > 0)->values();

        return view('refunds.create', compact('invoice', 'lines'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        $request->validate([
            'type'          => 'required|in:cash,credit',
            'notes'         => 'nullable|string|max:500',
            'items'         => 'required|array|min:1',
            'items.*.item_id'    => 'required|exists:items,id',
            'items.*.quantity'   => 'nullable|integer|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice->load(['items.item', 'refunds.items']);

        // Build refunded-so-far map
        $refundedQty = [];
        foreach ($invoice->refunds as $refund) {
            foreach ($refund->items as $ri) {
                $refundedQty[$ri->item_id] = ($refundedQty[$ri->item_id] ?? 0) + $ri->quantity;
            }
        }

        // Validate and collect lines with quantity > 0
        $lines = [];
        foreach ($request->items as $row) {
            $qty = (int) $row['quantity'];
            if ($qty <= 0) continue;

            $itemId = (int) $row['item_id'];
            $invoiceLine = $invoice->items->firstWhere('item_id', $itemId);
            if (! $invoiceLine) abort(422, 'Item not on this invoice.');

            $alreadyRefunded = $refundedQty[$itemId] ?? 0;
            $refundable = $invoiceLine->quantity - $alreadyRefunded;
            if ($qty > $refundable) abort(422, 'Quantity exceeds refundable amount.');

            $lines[] = [
                'item_id'    => $itemId,
                'quantity'   => $qty,
                'unit_price' => (float) $row['unit_price'],
                'subtotal'   => round((float) $row['unit_price'] * $qty, 2),
            ];
        }

        if (empty($lines)) {
            return back()->withErrors(['items' => 'Select at least one item to refund.']);
        }

        $total = array_sum(array_column($lines, 'subtotal'));

        DB::transaction(function () use ($invoice, $lines, $total, $request) {
            $refund = Refund::create([
                'invoice_id'   => $invoice->id,
                'refund_date'  => now()->toDateString(),
                'type'         => $request->type,
                'total_amount' => $total,
                'notes'        => $request->notes,
            ]);

            foreach ($lines as $line) {
                RefundItem::create([
                    'refund_id'  => $refund->id,
                    'item_id'    => $line['item_id'],
                    'quantity'   => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal'   => $line['subtotal'],
                ]);

                // Restore stock
                DB::statement(
                    'UPDATE items SET stock = CAST(stock AS SIGNED) + ? WHERE id = ?',
                    [$line['quantity'], $line['item_id']]
                );
            }

        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Refund recorded successfully.');
    }
}
