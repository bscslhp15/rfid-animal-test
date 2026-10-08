<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vaccination;

class VaccinationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff', 'veterinarian']);
    }

    public function view(User $user, Vaccination $vaccination): bool
    {
        return $user->hasAnyRole(['admin', 'staff', 'veterinarian']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff', 'veterinarian']);
    }

    public function update(User $user, Vaccination $vaccination): bool
    {
        return $user->hasAnyRole(['admin', 'staff', 'veterinarian']);
    }

    public function delete(User $user, Vaccination $vaccination): bool
    {
        return $user->hasRole('admin');
    }
}
