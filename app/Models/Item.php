<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = ['category_id', 'name', 'barcode', 'price', 'cost_price', 'image', 'stock', 'low_stock_threshold', 'expiry_date'];

    protected $casts = ['price' => 'decimal:2', 'cost_price' => 'decimal:2', 'expiry_date' => 'date'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function dealerPurchaseItems(): HasMany
    {
        return $this->hasMany(DealerPurchaseItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->low_stock_threshold;
    }

    public function daysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) return null;
        $today  = now()->startOfDay();
        $expiry = \Carbon\Carbon::parse($this->expiry_date)->startOfDay();
        $diff   = (int) $today->diffInDays($expiry);
        return $expiry->gte($today) ? $diff : -$diff;
    }

    public function expiryStatus(): string
    {
        $days = $this->daysUntilExpiry();
        if ($days === null) return 'none';
        if ($days < 0)     return 'expired';
        if ($days <= 30)   return 'soon';
        return 'ok';
    }

    public function profit(): ?float
    {
        if ($this->cost_price === null) return null;
        return $this->price - $this->cost_price;
    }

    public function marginPercent(): ?float
    {
        if ($this->cost_price === null || $this->price == 0) return null;
        return round(($this->price - $this->cost_price) / $this->price * 100, 1);
    }
}
