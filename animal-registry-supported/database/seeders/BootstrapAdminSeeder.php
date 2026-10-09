<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Species;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\Models\Role;

class BootstrapAdminSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('animal_categories') as $category => $definition) {
            foreach ($definition['species'] as $speciesName) {
                Species::firstOrCreate(['name' => $speciesName, 'category' => $category]);
            }
        }

        $email = strtolower(trim((string) env('ADMIN_EMAIL')));

        if ($email === '') {
            return;
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

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