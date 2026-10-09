<?php

namespace App\Policies;

use App\Models\Animal;
use App\Models\User;

class AnimalPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->isOwnerAccount() && $user->roles()->whereIn('name', ['admin', 'staff', 'veterinarian'])->exists();
    }

    public function view(User $user, Animal $animal): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'staff', 'veterinarian'])->exists();
    }

    public function viewOwn(User $user): bool
    {
        return $user->isOwnerAccount();
    }

    public function create(User $user): bool
    {
        return ! $user->isOwnerAccount() && $user->roles()->whereIn('name', ['admin', 'staff'])->exists();
    }

    public function update(User $user, Animal $animal): bool
    {
        return ! $user->isOwnerAccount() && $user->roles()->whereIn('name', ['admin', 'staff'])->exists();
    }

    public function addRecords(User $user, Animal $animal): bool
    {
        return ! $user->isOwnerAccount() && $user->roles()->whereIn('name', ['admin', 'staff', 'veterinarian'])->exists();
    }

    public function delete(User $user, Animal $animal): bool
    {
        return ! $user->isOwnerAccount() && $user->roles()->whereIn('name', ['admin', 'staff'])->exists();
    }

    public function viewOwnerContact(User $user, Animal $animal): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'staff', 'veterinarian'])->exists();
    }
}
