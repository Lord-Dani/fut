<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'company_id', 
        'dietary_preferences', 'telegram_chat_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'dietary_preferences' => 'array',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function dishes()
    {
        return $this->hasMany(Dish::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function mealPlans()
    {
        return $this->hasMany(MealPlan::class);
    }

    public function dishSuggestions()
    {
        return $this->hasMany(DishSuggestion::class);
    }

    // Scopes
    public function scopeEmployees($query)
    {
        return $query->where('role', 'employee');
    }

    public function scopeManagers($query)
    {
        return $query->where('role', 'manager');
    }

    // Methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }
}