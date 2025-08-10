<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AhpResult;
use Illuminate\Auth\Access\HandlesAuthorization;

class AhpResultPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Super Admin can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Tim Pengadaan can view all
        if ($user->hasRole('Tim Pengadaan')) {
            return true;
        }

        // Kaprodi can view all (read-only)
        if ($user->hasRole('Kaprodi')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AhpResult $ahpResult): bool
    {
        // Super Admin can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Tim Pengadaan can view all
        if ($user->hasRole('Tim Pengadaan')) {
            return true;
        }

        // Kaprodi can view all (read-only)
        if ($user->hasRole('Kaprodi')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Super Admin cannot create
        if ($user->hasRole('super_admin')) {
            return false;
        }

        // Only Tim Pengadaan can create
        return $user->hasRole('Tim Pengadaan');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AhpResult $ahpResult): bool
    {
        // Super Admin cannot update
        if ($user->hasRole('super_admin')) {
            return false;
        }

        // Only Tim Pengadaan can update
        return $user->hasRole('Tim Pengadaan');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AhpResult $ahpResult): bool
    {
        // Super Admin cannot delete
        if ($user->hasRole('super_admin')) {
            return false;
        }

        // Only Tim Pengadaan can delete
        return $user->hasRole('Tim Pengadaan');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AhpResult $ahpResult): bool
    {
        // Super Admin cannot restore
        if ($user->hasRole('super_admin')) {
            return false;
        }

        // Only Tim Pengadaan can restore
        return $user->hasRole('Tim Pengadaan');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AhpResult $ahpResult): bool
    {
        // Super Admin cannot force delete
        if ($user->hasRole('super_admin')) {
            return false;
        }

        // Only Tim Pengadaan can force delete
        return $user->hasRole('Tim Pengadaan');
    }
}
