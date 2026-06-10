<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DealerPurchase extends Model
{
    protected $fillable = ['dealer_id', 'purchase_date', 'total_amount', 'notes'];

    protected $casts = ['purchase_date' => 'date', 'total_amount' => 'decimal:2'];

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DealerPurchaseItem::class);
    }
}
