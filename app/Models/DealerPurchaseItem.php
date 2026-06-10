<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerPurchaseItem extends Model
{
    protected $fillable = ['dealer_purchase_id', 'item_id', 'quantity', 'unit_cost', 'subtotal'];

    protected $casts = ['unit_cost' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(DealerPurchase::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
