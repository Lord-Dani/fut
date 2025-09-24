<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'planned_for', 'planned_time', 'status', 'total_amount', 'notes',
        'user_id', 'company_id'
    ];

    protected $casts = [
        'planned_for' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('planned_for', today());
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Methods
    public function updateTotalAmount()
    {
        $this->total_amount = $this->items->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
        $this->save();
    }
}