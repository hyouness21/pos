<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['type', 'amount', 'notes', 'date'];

    protected $casts = [
        'amount' => 'decimal:2',
        'date'   => 'date',
    ];

    public static array $types = [
        'fuel'      => 'Fuel',
        'salary'    => 'Employee Salary',
        'item_cost' => 'Item Cost',
        'other'     => 'Other',
    ];

    public function typeLabel(): string
    {
        return self::$types[$this->type] ?? ucfirst($this->type);
    }
}
