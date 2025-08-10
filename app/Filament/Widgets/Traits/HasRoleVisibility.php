<?php

namespace App\Filament\Widgets\Traits;

use Illuminate\Support\Facades\Auth;

trait HasRoleVisibility
{
    public static function canView(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Get the required roles for this widget
        $requiredRoles = static::getRequiredRoles();
        
        // Check if user has any of the required roles
        foreach ($requiredRoles as $role) {
            if ($user->roles->contains('name', $role)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Override this method in your widget to specify required roles
     */
    protected static function getRequiredRoles(): array
    {
        return [];
    }
}
