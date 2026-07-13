<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Dealer;
use App\Models\DealerPurchase;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DealerPurchaseController extends Controller
{
    public function create(Dealer $dealer): View
    {
        $items      = Item::with('category')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        return view('dealer-purchases.create', compact('dealer', 'items', 'categories'));
    }

    public function store(Request $request, Dealer $dealer): RedirectResponse
    {
        $request->validate([
            'purchase_date'           => 'required|date',
            'notes'                   => 'nullable|string',
            'lines'                   => 'required|array|min:1',
            'lines.*.item_id'           => 'nullable|exists:items,id',
            'lines.*.new_name'          => 'nullable|string|max:255',
            'lines.*.new_category_id'   => 'nullable|exists:categories,id',
            'lines.*.new_category_name' => 'nullable|string|max:255',
            'lines.*.new_price'         => 'nullable|numeric|min:0',
            'lines.*.expiry_date'       => 'nullable|date',
            'lines.*.quantity'          => 'required|integer|min:1',
            'lines.*.unit_cost'         => 'required|numeric|min:0',
            'lines.*.total_paid'        => 'nullable|numeric|min:0',
        ]);

        $lines = $request->lines;

        DB::transaction(function () use (&$lines, $request, $dealer) {
            // Create any new items inside the transaction so they roll back on failure
            foreach ($lines as &$line) {
                if (empty($line['item_id'])) {
                    $categoryId = $line['new_category_id'] ?: null;

                    if (!empty($line['new_category_name'])) {
                        $categoryId = Category::firstOrCreate(
                            ['name' => trim($line['new_category_name'])]
                        )->id;
                    }

                    $item = Item::create([
                        'name'        => $line['new_name'],
                        'category_id' => $categoryId,
                        'price'       => (float) ($line['new_price'] ?? 0),
                        'cost_price'  => (float) $line['unit_cost'],
                        'stock'       => 0,
                        'expiry_date' => $line['expiry_date'] ?: null,
                    ]);
                    $line['item_id'] = $item->id;
                }
            }
            unset($line);
            $total = 0;
            foreach ($lines as $line) {
                $total += (float) ($line['total_paid'] ?? $line['unit_cost'] * $line['quantity']);
            }

            $purchase = $dealer->purchases()->create([
                'purchase_date' => $request->purchase_date,
                'total_amount'  => $total,
                'notes'         => $request->notes,
            ]);

            foreach ($lines as $line) {
                $purchase->items()->create([
                    'item_id'   => $line['item_id'],
                    'quantity'  => $line['quantity'],
                    'unit_cost' => $line['unit_cost'],
                    'subtotal'  => (float) ($line['total_paid'] ?? $line['unit_cost'] * $line['quantity']),
                ]);

                Item::where('id', $line['item_id'])->increment('stock', $line['quantity']);
            }
        });

        return redirect()->route('dealers.show', $dealer)->with('success', 'Purchase recorded.');
    }

    public function edit(DealerPurchase $dealerPurchase): View
    {
        $dealerPurchase->load('dealer', 'items.item');
        $items      = Item::with('category')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        $existingLines = $dealerPurchase->items->map(fn($l) => [
            'item_id'    => $l->item_id,
            'name'       => $l->item->name,
            'quantity'   => (int) $l->quantity,
            'unit_cost'  => (float) $l->unit_cost,
            'total_paid' => (float) $l->subtotal,
            'is_new'     => false,
        ])->values();

        return view('dealer-purchases.edit', compact('dealerPurchase', 'items', 'categories', 'existingLines'));
    }

    public function update(Request $request, DealerPurchase $dealerPurchase): RedirectResponse
    {
        $request->validate([
            'purchase_date'           => 'required|date',
            'notes'                   => 'nullable|string',
            'lines'                   => 'required|array|min:1',
            'lines.*.item_id'         => 'nullable|exists:items,id',
            'lines.*.new_name'        => 'nullable|string|max:255',
            'lines.*.new_category_id' => 'nullable|exists:categories,id',
            'lines.*.new_category_name' => 'nullable|string|max:255',
            'lines.*.new_price'       => 'nullable|numeric|min:0',
            'lines.*.expiry_date'     => 'nullable|date',
            'lines.*.quantity'        => 'required|integer|min:1',
            'lines.*.unit_cost'       => 'required|numeric|min:0',
            'lines.*.total_paid'      => 'nullable|numeric|min:0',
        ]);

        $lines = $request->lines;

        DB::transaction(function () use (&$lines, $request, $dealerPurchase) {
            // Create any new items inside the transaction so they roll back on failure
            foreach ($lines as &$line) {
                if (empty($line['item_id'])) {
                    $categoryId = $line['new_category_id'] ?: null;
                    if (!empty($line['new_category_name'])) {
                        $categoryId = Category::firstOrCreate(
                            ['name' => trim($line['new_category_name'])]
                        )->id;
                    }
                    $item = Item::create([
                        'name'        => $line['new_name'],
                        'category_id' => $categoryId,
                        'price'       => (float) ($line['new_price'] ?? 0),
                        'cost_price'  => (float) $line['unit_cost'],
                        'stock'       => 0,
                        'expiry_date' => $line['expiry_date'] ?: null,
                    ]);
                    $line['item_id'] = $item->id;
                }
            }
            unset($line);

            // Cast to SIGNED before subtracting so unsigned underflow cannot occur
            foreach ($dealerPurchase->items as $old) {
                DB::statement(
                    'UPDATE items SET stock = GREATEST(0, CAST(stock AS SIGNED) - ?) WHERE id = ?',
                    [(int) $old->quantity, $old->item_id]
                );
            }

            $dealerPurchase->items()->delete();

            $total = 0;
            foreach ($lines as $line) {
                $total += (float) ($line['total_paid'] ?? $line['unit_cost'] * $line['quantity']);
            }

            $dealerPurchase->update([
                'purchase_date' => $request->purchase_date,
                'total_amount'  => $total,
                'notes'         => $request->notes,
            ]);

            foreach ($lines as $line) {
                $dealerPurchase->items()->create([
                    'item_id'   => $line['item_id'],
                    'quantity'  => $line['quantity'],
                    'unit_cost' => $line['unit_cost'],
                    'subtotal'  => (float) ($line['total_paid'] ?? $line['unit_cost'] * $line['quantity']),
                ]);
                Item::where('id', $line['item_id'])->increment('stock', $line['quantity']);
            }
        });

        return redirect()->route('dealer-purchases.show', $dealerPurchase)->with('success', 'Purchase updated.');
    }

    public function show(DealerPurchase $dealerPurchase): View
    {
        $dealerPurchase->load('dealer', 'items.item');
        return view('dealer-purchases.show', compact('dealerPurchase'));
    }

    public function destroy(DealerPurchase $dealerPurchase): RedirectResponse
    {
        $dealer = $dealerPurchase->dealer;
        $dealerPurchase->delete();

        return redirect()->route('dealers.show', $dealer)->with('success', 'Purchase deleted.');
    }
}
