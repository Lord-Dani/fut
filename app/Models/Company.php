<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', 'slug', 'nutrition_budget', 'employee_limit', 'order_deadline'
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function dishes()
    {
        return $this->hasMany(Dish::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function dishSuggestions()
    {
        return $this->hasMany(DishSuggestion::class);
    }

    // Methods
    public function getActiveEmployeesCount()
    {
        return $this->users()->employees()->count();
    }

    public function getTodayOrders()
    {
        return $this->orders()->whereDate('planned_for', today())->get();
    }
}