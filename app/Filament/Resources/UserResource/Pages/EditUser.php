<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $rolesToAssign = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove roles from data as we'll handle it separately
        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        // Store roles for later assignment
        $this->rolesToAssign = $roles;

        return $data;
    }

    protected function afterSave(): void
    {
        // Assign role to the user
        if ($this->rolesToAssign) {
            // Remove existing roles first
            $this->record->syncRoles([]);

            // Assign new role
            $role = Role::where('name', $this->rolesToAssign)->first();
            if ($role) {
                $this->record->assignRole($role);
            }
        }
    }
}
