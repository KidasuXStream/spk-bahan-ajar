<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $rolesToAssign = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove roles from data as we'll handle it separately
        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        // Store roles for later assignment
        $this->rolesToAssign = $roles;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Assign role to the newly created user
        if ($this->rolesToAssign) {
            $role = Role::where('name', $this->rolesToAssign)->first();
            if ($role) {
                $this->record->assignRole($role);
            }
        }
    }
}
