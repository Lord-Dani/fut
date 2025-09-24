<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DishSuggestion extends Model
{
    protected $fillable = [
        'name', 'description', 'category', 'calories', 'price', 'restaurant',
        'justification', 'status', 'admin_notes', 'user_id', 'company_id', 'admin_id'
    ];

    protected $casts = [
        'calories' => 'integer',
        'price' => 'decimal:2',
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

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Methods
    public function approve($adminId, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'admin_id' => $adminId,
            'admin_notes' => $notes
        ]);
    }

    public function reject($adminId, $notes = null)
    {
        $this->update([
            'status' => 'rejected', 
            'admin_id' => $adminId,
            'admin_notes' => $notes
        ]);
    }
}