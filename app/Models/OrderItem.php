<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'quantity', 'unit_price', 'order_id', 'dish_id'
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }

    // Methods
    public function getTotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
}