<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = ['customer_id', 'status', 'payment_method', 'total_amount', 'discount', 'amount_paid', 'notes'];

    protected $casts = ['total_amount' => 'decimal:2', 'discount' => 'decimal:2', 'amount_paid' => 'decimal:2'];

    public function remaining(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->amount_paid);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
