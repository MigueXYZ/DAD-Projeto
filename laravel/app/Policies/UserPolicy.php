<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Allow everyone to view users
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Allow a user to view their own profile, or if they are an admin
        return $user->id === $model->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Allow everyone to create a user
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Allow a user to update their own profile, or if they are an admin
        return $user->id === $model->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Permitir que o próprio utilizador apague sua conta, exceto se for admin
        if ($user->id === $model->id && !$user->isAdmin()) {
            return true;
        }

        // Permitir que um admin apague qualquer utilizador, exceto outro admin
        if ($user->isAdmin() && !$model->isAdmin()) {
            return true;
        }

        if ($user->isAdmin() && $model->isAdmin() && $user!==$model) {
            return true;
        }
        


        return false;
    }


    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        // Allow the admin to restore any user
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Allow the admin to permanently delete any user
        return $user->isAdmin();
    }
}
