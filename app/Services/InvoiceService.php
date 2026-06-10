<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function create(array $data, array $lines): Invoice
    {
        return DB::transaction(function () use ($data, $lines) {
            $total = 0;
            foreach ($lines as $line) {
                $total += $line['unit_price'] * $line['quantity'];
            }

            $discount    = (float) ($data['discount'] ?? 0);
            $grandTotal  = max(0, $total - $discount);
            $invoice = Invoice::create([
                'customer_id'    => $data['customer_id'],
                'status'         => $data['payment_method'] === 'cash' ? 'paid' : 'pending',
                'payment_method' => $data['payment_method'],
                'total_amount'   => $grandTotal,
                'discount'       => $discount,
                'amount_paid'    => $data['payment_method'] === 'cash' ? $grandTotal : 0,
                'notes'          => $data['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                $invoice->items()->create([
                    'item_id'    => $line['item_id'],
                    'quantity'   => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal'   => $line['unit_price'] * $line['quantity'],
                ]);

                // Use GREATEST to never go below 0 on unsigned stock column
                Item::where('id', $line['item_id'])
                    ->update(['stock' => DB::raw('GREATEST(CAST(stock AS SIGNED) - ' . (int) $line['quantity'] . ', 0)')]);
            }

            return $invoice;
        });
    }

    public function markPaid(Invoice $invoice): void
    {
        $invoice->update(['status' => 'paid']);
    }

    public function markPending(Invoice $invoice): void
    {
        $invoice->update(['status' => 'pending']);
    }
}
