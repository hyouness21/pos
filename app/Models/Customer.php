<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address', 'credit_balance'];

    protected $casts = ['credit_balance' => 'decimal:2'];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function totalOwed(): float
    {
        return $this->invoices()->where('status', 'pending')->sum('total_amount');
    }

    public function totalPurchases(): float
    {
        return $this->invoices()->sum('total_amount');
    }
}
