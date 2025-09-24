<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    protected $fillable = [
        'name', 'description', 'category', 'calories', 'protein', 'fat', 'carbs',
        'price', 'allergens', 'restaurant', 'image_url', 'is_global', 'is_active',
        'company_id', 'user_id'
    ];

    protected $casts = [
        'allergens' => 'array',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function mealPlans()
    {
        return $this->hasMany(MealPlan::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->orWhere('is_global', true);
        });
    }
}