<?php

namespace App\Policies;

use App\Models\Animal;
use App\Models\User;

class AnimalPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->hasRole('owner') && $user->hasAnyRole(['admin', 'staff', 'veterinarian']);
    }

    public function view(User $user, Animal $animal): bool
    {
        return ! $user->hasRole('owner') && $user->hasAnyRole(['admin', 'staff', 'veterinarian']);
    }

    public function create(User $user): bool
    {
        return ! $user->hasRole('owner') && $user->hasAnyRole(['admin', 'staff']);
    }

    public function update(User $user, Animal $animal): bool
    {
        return ! $user->hasRole('owner') && $user->hasAnyRole(['admin', 'staff']);
    }

    public function addRecords(User $user, Animal $animal): bool
    {
        return ! $user->hasRole('owner') && $user->hasAnyRole(['admin', 'staff', 'veterinarian']);
    }

    public function delete(User $user, Animal $animal): bool
    {
        return ! $user->hasRole('owner') && $user->hasAnyRole(['admin', 'staff']);
    }

    public function viewOwnerContact(User $user, Animal $animal): bool
    {
        return ! $user->hasRole('owner') && $user->hasAnyRole(['admin', 'staff', 'veterinarian']);
    }
}
