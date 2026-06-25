<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function create(array $data, array $lines, ?string $invoiceDate = null): Invoice
    {
        return DB::transaction(function () use ($data, $lines, $invoiceDate) {
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

            if ($invoiceDate) {
                $invoice->timestamps = false;
                $invoice->created_at = \Carbon\Carbon::parse($invoiceDate)->setTimeFrom(now());
                $invoice->save();
                $invoice->timestamps = true;
            }

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

    public function update(Invoice $invoice, array $data, array $lines): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $lines) {
            foreach ($invoice->items as $old) {
                Item::where('id', $old->item_id)
                    ->update(['stock' => DB::raw('stock + ' . (int) $old->quantity)]);
            }

            $invoice->items()->delete();

            $total = 0;
            foreach ($lines as $line) {
                $total += $line['unit_price'] * $line['quantity'];
            }
            $discount   = (float) ($data['discount'] ?? 0);
            $grandTotal = max(0, $total - $discount);

            $invoice->update([
                'payment_method' => $data['payment_method'],
                'notes'          => $data['notes'] ?? null,
                'discount'       => $discount,
                'total_amount'   => $grandTotal,
                'status'         => $data['payment_method'] === 'cash' ? 'paid' : 'pending',
                'amount_paid'    => $data['payment_method'] === 'cash' ? $grandTotal : 0,
            ]);

            foreach ($lines as $line) {
                $invoice->items()->create([
                    'item_id'    => $line['item_id'],
                    'quantity'   => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal'   => $line['unit_price'] * $line['quantity'],
                ]);

                Item::where('id', $line['item_id'])
                    ->update(['stock' => DB::raw('GREATEST(CAST(stock AS SIGNED) - ' . (int) $line['quantity'] . ', 0)')]);
            }

            return $invoice->fresh();
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
