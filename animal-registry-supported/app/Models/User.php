<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function ownedAnimals(): HasMany
    {
        return $this->hasMany(Animal::class, 'owner_user_id');
    }

    public function scopeOwners(Builder $query): Builder
    {
        return $query->whereHas('roles', fn (Builder $roleQuery) => $roleQuery
            ->where('name', 'owner')
            ->where('guard_name', 'web'));
    }

    public function isOwnerAccount(): bool
    {
        return $this->roles()->where('name', 'owner')->exists();
    }

    public function homeRouteName(): string
    {
        return $this->isOwnerAccount()
            ? 'my-animals.index'
            : 'dashboard';
    }
}
