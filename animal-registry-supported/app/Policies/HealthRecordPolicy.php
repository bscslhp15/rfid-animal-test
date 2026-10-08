<?php

namespace App\Policies;

use App\Models\HealthRecord;
use App\Models\User;

class HealthRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff', 'veterinarian']);
    }

    public function view(User $user, HealthRecord $healthRecord): bool
    {
        return $user->hasAnyRole(['admin', 'staff', 'veterinarian']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff', 'veterinarian']);
    }

    public function update(User $user, HealthRecord $healthRecord): bool
    {
        return $user->hasAnyRole(['admin', 'staff', 'veterinarian']);
    }

    public function delete(User $user, HealthRecord $healthRecord): bool
    {
        return $user->hasRole('admin');
    }
}
