<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dealer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address', 'notes'];

    public function purchases(): HasMany
    {
        return $this->hasMany(DealerPurchase::class);
    }

    public function totalSpent(): float
    {
        return $this->purchases()->sum('total_amount');
    }
}
