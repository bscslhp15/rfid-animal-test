<?php

namespace Database\Seeders;

use App\Models\Species;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (config('animal_categories') as $category => $definition) {
            foreach ($definition['species'] as $speciesName) {
                Species::firstOrCreate(['name' => $speciesName, 'category' => $category]);
            }
        }

        $roles = ['admin', 'staff', 'veterinarian', 'owner'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'email_verified_at' => now(), 'password' => Hash::make('password')]
        );
        $admin->syncRoles(['admin']);

        $staff = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Staff User', 'email_verified_at' => now(), 'password' => Hash::make('password')]
        );
        $staff->syncRoles(['staff']);
    }
}
