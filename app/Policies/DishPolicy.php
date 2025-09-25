<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Dish;

class DishPolicy
{
    public function update(User $user, Dish $dish)
    {
        return $user->isAdmin() || 
               ($user->isManager() && $dish->company_id === $user->company_id) ||
               $dish->user_id === $user->id;
    }

    public function delete(User $user, Dish $dish)
    {
        return $user->isAdmin() || 
               ($user->isManager() && $dish->company_id === $user->company_id) ||
               $dish->user_id === $user->id;
    }
}