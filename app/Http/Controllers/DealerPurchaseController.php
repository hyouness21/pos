<?php

namespace App\Http\Controllers;

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
        $items = Item::with('category')->orderBy('name')->get();
        return view('dealer-purchases.create', compact('dealer', 'items'));
    }

    public function store(Request $request, Dealer $dealer): RedirectResponse
    {
        $request->validate([
            'purchase_date'        => 'required|date',
            'notes'                => 'nullable|string',
            'lines'                => 'required|array|min:1',
            'lines.*.item_id'      => 'required|exists:items,id',
            'lines.*.quantity'     => 'required|integer|min:1',
            'lines.*.unit_cost'    => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $dealer) {
            $total = 0;
            foreach ($request->lines as $line) {
                $total += $line['unit_cost'] * $line['quantity'];
            }

            $purchase = $dealer->purchases()->create([
                'purchase_date' => $request->purchase_date,
                'total_amount'  => $total,
                'notes'         => $request->notes,
            ]);

            foreach ($request->lines as $line) {
                $purchase->items()->create([
                    'item_id'   => $line['item_id'],
                    'quantity'  => $line['quantity'],
                    'unit_cost' => $line['unit_cost'],
                    'subtotal'  => $line['unit_cost'] * $line['quantity'],
                ]);

                Item::where('id', $line['item_id'])->increment('stock', $line['quantity']);
            }
        });

        return redirect()->route('dealers.show', $dealer)->with('success', 'Purchase recorded.');
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
