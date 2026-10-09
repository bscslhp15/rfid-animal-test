<?php

namespace Database\Seeders;

use App\Models\Species;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\Models\Role;

class BootstrapAdminSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'staff', 'veterinarian', 'owner'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        foreach (config('animal_categories') as $category => $definition) {
            foreach ($definition['species'] as $speciesName) {
                Species::firstOrCreate(['name' => $speciesName, 'category' => $category]);
            }
        }

        $email = strtolower(trim((string) env('ADMIN_EMAIL')));

        if ($email === '') {
            return;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $password = (string) env('ADMIN_PASSWORD');

            if ($password === '') {
                throw new RuntimeException('Set ADMIN_PASSWORD to create the configured admin account.');
            }

            $user = new User;
            $user->name = 'Administrator';
            $user->email = $email;
            $user->password = Hash::make($password);
        }

        $user->email_verified_at ??= now();
        $user->save();
        $user->syncRoles(['admin']);
    }
}
