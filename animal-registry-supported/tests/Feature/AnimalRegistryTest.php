<?php

use App\Models\Animal;
use App\Models\Alert;
use App\Models\FeedingLog;
use App\Models\ScanLog;
use App\Models\Species;
use App\Models\User;
use Database\Seeders\BootstrapAdminSeeder;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('admin can register an animal with a QR token', function () {
    Role::firstOrCreate(['name' => 'admin']);

    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');
    $species = Species::factory()->create(['name' => 'Dog']);

    $this->actingAs($admin)
        ->post('/animals', [
            'name' => 'Rex',
            'species_id' => $species->id,
            'sex' => 'male',
            'breed' => 'Alsatian',
            'owner_name' => 'Maria Santos',
            'owner_phone' => '09171234567',
            'owner_address' => 'Barangay San Jose, Davao City',
            'status' => 'active',
        ])
        ->assertRedirect('/animals');

    $this->assertDatabaseHas('animals', [
        'name' => 'Rex',
        'species_id' => $species->id,
        'status' => 'active',
    ]);
});

test('animal registration shows vaccination status separately from animal status', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $this->actingAs($staff)
        ->get('/animals/create')
        ->assertOk()
        ->assertSee('Vaccinated?')
        ->assertSee('Not vaccinated')
        ->assertSee('List of vaccines')
        ->assertSee('hasVaccinations === \'1\'', false);
});

test('animals can be filtered by saved vaccination records', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');
    $species = Species::factory()->create(['name' => 'Dog', 'category' => 'companion']);

    $vaccinatedAnimal = Animal::create([
        'name' => 'Vaccinated Rex',
        'species_id' => $species->id,
        'owner_name' => 'Owner One',
        'owner_phone' => '09170000001',
        'owner_address' => 'Address One',
        'status' => 'active',
    ]);
    $vaccinatedAnimal->vaccinations()->create([
        'vaccine_name' => 'Rabies',
        'given_on' => now()->toDateString(),
    ]);

    Animal::create([
        'name' => 'Unvaccinated Luna',
        'species_id' => $species->id,
        'owner_name' => 'Owner Two',
        'owner_phone' => '09170000002',
        'owner_address' => 'Address Two',
        'status' => 'active',
    ]);

    $this->actingAs($staff)
        ->get('/animals?vaccination=vaccinated')
        ->assertOk()
        ->assertSee('Vaccinated Rex')
        ->assertDontSee('Unvaccinated Luna');

    $this->actingAs($staff)
        ->get('/animals?vaccination=not_vaccinated')
        ->assertOk()
        ->assertSee('Unvaccinated Luna')
        ->assertDontSee('Vaccinated Rex');
});

test('staff can register an animal even when vaccination dates are left blank', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $species = Species::factory()->create(['name' => 'Dog']);

    $this->actingAs($staff)
        ->post('/animals', [
            'name' => 'Pogi',
            'species_id' => $species->id,
            'sex' => 'male',
            'breed' => 'Shih Tzu',
            'owner_name' => 'Maria Santos',
            'owner_phone' => '09171234567',
            'owner_address' => 'Davao City',
            'status' => 'active',
            'vaccinations' => [[
                'vaccine_name' => 'Rabies',
                'given_on' => '',
                'next_due_on' => '',
            ]],
        ])
        ->assertRedirect('/animals');

    $this->assertDatabaseHas('animals', ['name' => 'Pogi']);
    $this->assertDatabaseHas('vaccinations', ['vaccine_name' => 'Rabies']);
});

test('newly registered users do not get staff access automatically and ordinary users cannot access the dashboard', function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $plainUser = User::factory()->create(['email_verified_at' => now()]);
    $plainUser->syncRoles([]);

    $this->actingAs($staff)->get('/dashboard')->assertOk();
    $this->actingAs($plainUser)->get('/dashboard')->assertForbidden();

    Livewire::test('pages.auth.register')
        ->set('name', 'New Plain User')
        ->set('email', 'new-user-'.uniqid().'@example.com')
        ->set('password', 'Password123!')
        ->set('password_confirmation', 'Password123!')
        ->call('register');

    $user = User::query()->where('email', 'like', 'new-user-%@example.com')->latest()->first();

    $this->assertNotNull($user);
    $this->assertFalse($user->hasRole('staff'));
    $this->actingAs($user)->get('/dashboard')->assertForbidden();
});

test('veterinarian users can access the dashboard but owner users cannot', function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'staff']);
    Role::firstOrCreate(['name' => 'veterinarian']);
    Role::firstOrCreate(['name' => 'owner']);

    $veterinarian = User::factory()->create(['email_verified_at' => now()]);
    $veterinarian->assignRole('veterinarian');

    $owner = User::factory()->create(['email_verified_at' => now()]);
    $owner->assignRole('owner');

    $this->actingAs($veterinarian)->get('/dashboard')->assertOk();
    $this->actingAs($owner)->get('/dashboard')->assertForbidden();
});

test('staff can add vaccination and temperature records and the dashboard shows due soon counts', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $species = Species::factory()->create(['name' => 'Dog']);
    $animal = Animal::create([
        'name' => 'Milo',
        'species_id' => $species->id,
        'sex' => 'male',
        'owner_name' => 'Ana Reyes',
        'owner_phone' => '09171122333',
        'owner_address' => 'Davao City',
        'status' => 'active',
    ]);

    $this->actingAs($staff)
        ->post("/animals/{$animal->id}/vaccinations", [
            'vaccine_name' => 'Rabies',
            'given_on' => '2026-10-01',
            'next_due_on' => '2026-10-15',
            'batch_no' => 'RAB-100',
            'administered_by' => 'Dr. Santos',
            'notes' => 'Routine vaccination',
        ])
        ->assertRedirect("/animals/{$animal->id}");

    $this->actingAs($staff)
        ->post("/animals/{$animal->id}/health-records", [
            'type' => 'temperature',
            'value_numeric' => '39.1',
            'unit' => 'C',
            'details' => 'Mild fever detected',
            'recorded_at' => '2026-10-02 09:00:00',
            'recorded_by' => 'Dr. Santos',
        ])
        ->assertRedirect("/animals/{$animal->id}");

    $this->actingAs($staff)
        ->post("/animals/{$animal->id}/health-records", [
            'type' => 'height',
            'value_numeric' => '42.5',
            'unit' => 'cm',
            'recorded_by' => 'Dr. Santos',
        ])
        ->assertRedirect("/animals/{$animal->id}");

    $this->assertDatabaseHas('vaccinations', [
        'animal_id' => $animal->id,
        'vaccine_name' => 'Rabies',
    ]);

    $this->assertDatabaseHas('health_records', [
        'animal_id' => $animal->id,
        'type' => 'temperature',
        'value_numeric' => 39.1,
    ]);
    $this->assertDatabaseHas('health_records', [
        'animal_id' => $animal->id,
        'type' => 'height',
        'value_numeric' => 42.5,
        'unit' => 'cm',
    ]);

    $this->actingAs($staff)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Vaccines due soon');
});

test('staff can mark an animal missing and scan history is logged for the public lookup route', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $species = Species::factory()->create(['name' => 'Dog']);
    $animal = Animal::create([
        'name' => 'Milo',
        'species_id' => $species->id,
        'sex' => 'male',
        'owner_name' => 'Ana Reyes',
        'owner_phone' => '09171122333',
        'owner_address' => 'Davao City',
        'status' => 'active',
    ]);

    $animal->tag()->create([
        'identifier' => 'missing-scan-token',
        'type' => 'qr',
        'status' => 'active',
        'assigned_at' => now(),
    ]);

    $this->actingAs($staff)
        ->post("/animals/{$animal->id}/missing", ['status' => 'missing'])
        ->assertRedirect("/animals/{$animal->id}");

    $this->assertDatabaseHas('animals', ['id' => $animal->id, 'status' => 'missing']);

    $this->get('/t/missing-scan-token')
        ->assertOk()
        ->assertSee('Missing');

    $this->assertDatabaseHas('scan_logs', [
        'tag_identifier' => 'missing-scan-token',
        'result' => 'found',
    ]);
});

test('staff can print a full animal record as a PDF', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $species = Species::factory()->create(['name' => 'Dog']);
    $animal = Animal::create([
        'name' => 'Bantay',
        'species_id' => $species->id,
        'sex' => 'male',
        'owner_name' => 'Luis Dela Cruz',
        'owner_phone' => '09175551234',
        'owner_address' => 'Cagayan De Oro',
        'status' => 'active',
    ]);

    $animal->tag()->create([
        'identifier' => 'pdf-token',
        'type' => 'qr',
        'status' => 'active',
        'assigned_at' => now(),
    ]);

    $this->actingAs($staff)
        ->get("/animals/{$animal->id}/print")
        ->assertOk();
});

test('each animal can download its QR code and the public record page shows a shareable link', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $species = Species::factory()->create(['name' => 'Dog']);
    $animal = Animal::create([
        'name' => 'Scout',
        'species_id' => $species->id,
        'sex' => 'male',
        'owner_name' => 'Liza Castro',
        'owner_phone' => '09170001122',
        'owner_address' => 'Cebu City',
        'status' => 'active',
    ]);

    $animal->tag()->create([
        'identifier' => 'download-token',
        'type' => 'qr',
        'status' => 'active',
        'assigned_at' => now(),
    ]);

    $this->actingAs($staff)
        ->get("/animals/{$animal->id}/qr/download")
        ->assertOk()
        ->assertHeader('content-type', 'image/svg+xml');

    $this->get('/t/download-token')
        ->assertOk()
        ->assertDontSee(url('/t/download-token'))
        ->assertDontSee('Scan or open this link');

    $this->actingAs($staff)
        ->get("/animals/{$animal->id}")
        ->assertOk()
        ->assertSee('Edit profile')
        ->assertSee(route('animals.update', $animal));
});

test('staff can register an animal with a unique pet code and the registry list shows its tag actions', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $species = Species::factory()->create(['name' => 'Dog']);

    $this->actingAs($staff)
        ->get('/animals/create')
        ->assertOk()
        ->assertSee('Name')
        ->assertSee('Date of birth')
        ->assertSee('List of vaccines')
        ->assertSee('Vaccine name')
        ->assertSee('Date given')
        ->assertSee('Next due date (optional)')
        ->assertSee('Temperature')
        ->assertSee('Height')
        ->assertSee('Weight');

    $this->actingAs($staff)
        ->post('/animals', [
            'name' => 'Niko',
            'species_id' => $species->id,
            'breed' => 'Shih Tzu',
            'sex' => 'male',
            'birthdate' => null,
            'owner_name' => 'Maria Santos',
            'owner_phone' => '09171234567',
            'owner_address' => 'Davao City',
            'status' => 'active',
            'vaccines' => 'Rabies, Distemper',
            'vaccinations' => [
                ['vaccine_name' => 'Parvo', 'given_on' => '2026-10-01', 'next_due_on' => '2027-10-01'],
            ],
            'temperature' => '38.5',
            'temperature_unit' => 'C',
            'height' => '42.5',
            'height_unit' => 'cm',
            'weight' => '12.4',
            'weight_unit' => 'kg',
        ])
        ->assertRedirect('/animals');

    $animal = Animal::query()->where('name', 'Niko')->latest()->first();

    $this->assertNotNull($animal);
    $this->assertNotNull($animal->pet_code);
    $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{8,}$/', $animal->pet_code);
    $this->assertDatabaseHas('vaccinations', ['animal_id' => $animal->id]);
    $this->assertDatabaseHas('vaccinations', ['animal_id' => $animal->id, 'vaccine_name' => 'Parvo']);
    $this->assertDatabaseHas('health_records', ['animal_id' => $animal->id, 'type' => 'temperature']);
    $this->assertDatabaseHas('health_records', ['animal_id' => $animal->id, 'type' => 'height', 'value_numeric' => 42.5]);
    $this->assertDatabaseHas('health_records', ['animal_id' => $animal->id, 'type' => 'weight', 'value_numeric' => 12.4]);

    $this->actingAs($staff)
        ->get('/animals')
        ->assertOk()
        ->assertSee($animal->pet_code)
        ->assertSee('View')
        ->assertSee('Edit')
        ->assertSee('Delete');
});

test('staff can update vaccine dates and measurements and the public QR page shows the full record without the contact notice', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $species = Species::factory()->create(['name' => 'Dog']);
    $animal = Animal::create([
        'name' => 'Bingo',
        'species_id' => $species->id,
        'breed' => 'Aspen',
        'sex' => 'male',
        'owner_name' => 'Rosa Cruz',
        'owner_phone' => '09175551234',
        'owner_address' => 'Quezon City',
        'status' => 'active',
    ]);
    $animal->tag()->create([
        'identifier' => 'full-record-token',
        'type' => 'qr',
        'status' => 'active',
        'assigned_at' => now(),
    ]);

    $this->actingAs($staff)
        ->get("/animals/{$animal->id}/edit")
        ->assertOk()
        ->assertSee('Vaccine name')
        ->assertSee('Date given')
        ->assertSee('Next due date (optional)')
        ->assertSee('Height')
        ->assertSee('Weight');

    $this->actingAs($staff)
        ->put("/animals/{$animal->id}", [
            'name' => 'Bingo',
            'species_id' => $species->id,
            'breed' => 'Aspen',
            'sex' => 'male',
            'owner_name' => 'Rosa Cruz',
            'owner_phone' => '09175551234',
            'owner_address' => 'Quezon City',
            'status' => 'active',
            'vaccinations' => [
                ['vaccine_name' => 'Rabies', 'given_on' => '2026-10-01', 'next_due_on' => '2027-10-01'],
            ],
            'height' => '42.5',
            'weight' => '12.4',
            'temperature' => '38.5',
        ])
        ->assertRedirect("/animals/{$animal->id}");

    $vaccination = $animal->vaccinations()->where('vaccine_name', 'Rabies')->firstOrFail();
    $this->assertSame('2026-10-01', $vaccination->given_on->format('Y-m-d'));
    $this->assertSame('2027-10-01', $vaccination->next_due_on->format('Y-m-d'));
    $this->assertDatabaseHas('health_records', ['animal_id' => $animal->id, 'type' => 'height', 'value_numeric' => 42.5]);
    $this->assertDatabaseHas('health_records', ['animal_id' => $animal->id, 'type' => 'weight', 'value_numeric' => 12.4]);

    $this->get('/t/full-record-token')
        ->assertOk()
        ->assertSee('Rosa Cruz')
        ->assertSee('09175551234')
        ->assertSee('Quezon City')
        ->assertSee('Rabies')
        ->assertSee('42.5')
        ->assertSee('12.4')
        ->assertDontSee('Contact the City Veterinary Office for owner details and verification.');
});

test('animal detail page renders vaccination rows even when the date given is blank', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $species = Species::factory()->create(['name' => 'Dog']);
    $animal = Animal::create([
        'name' => 'Blank Date Dog',
        'species_id' => $species->id,
        'breed' => 'Aspin',
        'sex' => 'male',
        'owner_name' => 'Cora Santos',
        'owner_phone' => '09178889999',
        'owner_address' => 'Bacolod City',
        'status' => 'active',
    ]);

    $animal->vaccinations()->create([
        'vaccine_name' => 'Rabies',
        'given_on' => null,
        'next_due_on' => null,
        'batch_no' => 'B-100',
        'notes' => 'Recorded without date',
    ]);

    $this->actingAs($staff)
        ->get("/animals/{$animal->id}")
        ->assertOk()
        ->assertSee('Rabies')
        ->assertSee('B-100')
        ->assertSee('Recorded without date')
        ->assertSee('Given: —');
});

test('staff can log a feeding record and view the feeding page', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $species = Species::factory()->create(['name' => 'Dog']);
    $animal = Animal::create([
        'name' => 'Feeding Test Dog',
        'species_id' => $species->id,
        'breed' => 'Aspin',
        'sex' => 'male',
        'owner_name' => 'Liza Castro',
        'owner_phone' => '09170001122',
        'owner_address' => 'Cebu City',
        'status' => 'active',
    ]);

    $this->actingAs($staff)
        ->post('/feeding', [
            'animal_id' => $animal->id,
            'feed_type' => 'Commercial feed',
            'quantity' => 2.5,
            'unit' => 'kg',
            'fed_at' => now()->toDateTimeString(),
            'notes' => 'Morning feed',
        ])
        ->assertRedirect('/feeding');

    $this->actingAs($staff)
        ->get('/feeding')
        ->assertOk()
        ->assertSee('Commercial feed')
        ->assertSee('Morning feed');
});

test('staff can open the analytics page and see the summary tiles', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $species = Species::factory()->create(['name' => 'Dog']);

    Animal::create([
        'name' => 'Analytics Dog',
        'species_id' => $species->id,
        'breed' => 'Aspin',
        'sex' => 'male',
        'owner_name' => 'Ana Reyes',
        'owner_phone' => '09170003333',
        'owner_address' => 'Davao',
        'status' => 'active',
    ]);

    $this->actingAs($staff)
        ->get('/analytics')
        ->assertOk()
        ->assertSee('Analytics')
        ->assertSee('Registered animals')
        ->assertSee('Vaccination coverage')
        ->assertSee('data-chart-type="doughnut"', false)
        ->assertSee('data-chart-type="pie"', false)
        ->assertSee('data-chart-type="bar"', false)
        ->assertSee('data-chart-type="line"', false);
});

test('analytics page stays accessible when the feeding logs table is missing', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    Species::factory()->create(['name' => 'Dog']);
    Schema::dropIfExists('feeding_logs');

    $this->actingAs($staff)
        ->get('/analytics')
        ->assertOk();

    Schema::create('feeding_logs', function ($table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('animal_id')->nullable()->constrained('animals')->cascadeOnDelete();
        $table->string('feed_type');
        $table->decimal('quantity', 8, 2)->default(0);
        $table->string('unit')->default('kg');
        $table->dateTime('fed_at')->nullable();
        $table->decimal('cost', 10, 2)->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();
    });
});

test('database seeding can be repeated without duplicating species or demo users', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(Species::query()->where('name', 'Dog')->where('category', 'companion')->count())->toBe(1);
    expect(User::query()->whereIn('email', ['admin@example.com', 'staff@example.com'])->count())->toBe(2);
});

test('configured bootstrap admin receives the admin role', function () {
    $admin = User::factory()->create(['email_verified_at' => null]);
    $originalEmail = getenv('ADMIN_EMAIL');

    putenv('ADMIN_EMAIL='.$admin->email);
    $_ENV['ADMIN_EMAIL'] = $admin->email;
    $_SERVER['ADMIN_EMAIL'] = $admin->email;

    try {
        $this->seed(BootstrapAdminSeeder::class);
    } finally {
        if ($originalEmail === false) {
            putenv('ADMIN_EMAIL');
            unset($_ENV['ADMIN_EMAIL'], $_SERVER['ADMIN_EMAIL']);
        } else {
            putenv('ADMIN_EMAIL='.$originalEmail);
            $_ENV['ADMIN_EMAIL'] = $originalEmail;
            $_SERVER['ADMIN_EMAIL'] = $originalEmail;
        }
    }

    expect($admin->fresh()->hasRole('admin'))->toBeTrue()
        ->and($admin->fresh()->email_verified_at)->not->toBeNull()
        ->and(Species::query()->where('name', 'Duck')->where('category', 'poultry')->exists())->toBeTrue();
});

test('animal profile measurements append history instead of replacing earlier readings', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');
    $species = Species::factory()->create(['name' => 'Dog', 'category' => 'companion']);
    $animal = Animal::create([
        'name' => 'Pochi',
        'species_id' => $species->id,
        'owner_name' => 'Lina Reyes',
        'owner_phone' => '09170001111',
        'owner_address' => 'Cebu City',
        'status' => 'active',
    ]);

    $this->actingAs($staff)->put("/animals/{$animal->id}", [
        'name' => 'Pochi',
        'species_id' => $species->id,
        'owner_name' => 'Lina Reyes',
        'owner_phone' => '09170001111',
        'owner_address' => 'Cebu City',
        'status' => 'active',
        'temperature' => '38.2',
        'height' => '40',
        'weight' => '8.5',
    ])->assertRedirect("/animals/{$animal->id}");

    $this->actingAs($staff)->put("/animals/{$animal->id}", [
        'name' => 'Pochi',
        'species_id' => $species->id,
        'owner_name' => 'Lina Reyes',
        'owner_phone' => '09170001111',
        'owner_address' => 'Cebu City',
        'status' => 'active',
        'temperature' => '39.1',
        'height' => '42',
        'weight' => '9.2',
    ])->assertRedirect("/animals/{$animal->id}");

    expect($animal->healthRecords()->where('type', 'temperature')->count())->toBe(2);
    expect($animal->healthRecords()->where('type', 'height')->count())->toBe(2);
    expect($animal->healthRecords()->where('type', 'weight')->count())->toBe(2);
});

test('staff and veterinarians can add health records and animal or vaccination removal is soft deleted', function () {
    Role::firstOrCreate(['name' => 'staff']);
    Role::firstOrCreate(['name' => 'veterinarian']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');
    $veterinarian = User::factory()->create(['email_verified_at' => now()]);
    $veterinarian->syncRoles(['veterinarian']);
    $species = Species::factory()->create(['name' => 'Dog', 'category' => 'companion']);
    $animal = Animal::create([
        'name' => 'Bantay',
        'species_id' => $species->id,
        'owner_name' => 'Rosa Cruz',
        'owner_phone' => '09172223333',
        'owner_address' => 'Davao City',
        'status' => 'active',
    ]);
    $vaccination = $animal->vaccinations()->create([
        'vaccine_name' => 'Rabies',
        'given_on' => '2026-10-01',
    ]);

    $this->actingAs($veterinarian)->post("/animals/{$animal->id}/health-records", [
        'type' => 'temperature',
        'value_numeric' => '38.5',
        'unit' => 'C',
    ])->assertRedirect("/animals/{$animal->id}");

    $this->actingAs($veterinarian)->post("/animals/{$animal->id}/vaccinations", [
        'vaccine_name' => 'Distemper',
        'given_on' => '2026-10-02',
    ])->assertRedirect("/animals/{$animal->id}");

    $this->actingAs($staff)->put("/animals/{$animal->id}", [
        'name' => $animal->name,
        'species_id' => $species->id,
        'owner_name' => $animal->owner_name,
        'owner_phone' => $animal->owner_phone,
        'owner_address' => $animal->owner_address,
        'status' => $animal->status,
        'vaccinations' => [
            ['id' => $vaccination->id, 'delete' => '1'],
        ],
    ])->assertRedirect("/animals/{$animal->id}");

    $this->actingAs($staff)->delete("/animals/{$animal->id}")->assertRedirect('/animals');

    $this->assertDatabaseHas('health_records', ['animal_id' => $animal->id, 'type' => 'temperature']);
    $this->assertDatabaseHas('vaccinations', ['animal_id' => $animal->id, 'vaccine_name' => 'Distemper']);
    $this->assertSoftDeleted('vaccinations', ['id' => $vaccination->id]);
    $this->assertSoftDeleted('animals', ['id' => $animal->id]);
});

test('animal listing filters by category, species, status, group, and search and paginates fifteen records', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');
    $dog = Species::factory()->create(['name' => 'Dog', 'category' => 'companion']);
    $cat = Species::factory()->create(['name' => 'Cat', 'category' => 'companion']);

    foreach (range(1, 16) as $number) {
        Animal::create([
            'name' => "Pen dog {$number}",
            'species_id' => $dog->id,
            'owner_name' => 'Lina Reyes',
            'owner_phone' => '09170001111',
            'owner_address' => 'Cebu City',
            'status' => 'active',
            'group_name' => 'Pen A',
        ]);
    }

    Animal::create([
        'name' => 'Other group cat',
        'species_id' => $cat->id,
        'owner_name' => 'Nilo Santos',
        'owner_phone' => '09170002222',
        'owner_address' => 'Davao City',
        'status' => 'missing',
        'group_name' => 'Pen B',
    ]);

    $response = $this->actingAs($staff)->get('/animals?category=companion&species='.$dog->id.'&status=active&group=Pen%20A&search=Pen%20dog');

    $response->assertOk()
        ->assertSee('min-w-[1480px]', false)
        ->assertSee('Group / details')
        ->assertSee('Quantity')
        ->assertViewHas('animals', function ($animals) {
            return $animals->total() === 16 && $animals->count() === 15;
        });
});

test('dashboard counts only the latest vaccination for each animal and vaccine', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');
    $species = Species::factory()->create(['name' => 'Dog', 'category' => 'companion']);
    $animal = Animal::create([
        'name' => 'Kiko',
        'species_id' => $species->id,
        'owner_name' => 'Nena Flores',
        'owner_phone' => '09173334444',
        'owner_address' => 'Iloilo City',
        'status' => 'active',
    ]);
    $animal->vaccinations()->create([
        'vaccine_name' => 'Rabies',
        'given_on' => today()->subYear(),
        'next_due_on' => today()->subDay(),
    ]);
    $animal->vaccinations()->create([
        'vaccine_name' => 'Rabies',
        'given_on' => today(),
        'next_due_on' => today()->addDays(3),
    ]);

    $this->actingAs($staff)->get('/dashboard')->assertOk()->assertViewHas('stats', function ($stats) {
        return $stats['due_soon'] === 1 && $stats['overdue'] === 0;
    });
});

test('dashboard shows upcoming vaccinations, recent scans, alerts and feeding activity', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');
    $species = Species::factory()->create(['name' => 'Dog', 'category' => 'companion']);
    $animal = Animal::create([
        'name' => 'Dashboard Kiko',
        'species_id' => $species->id,
        'owner_name' => 'Nena Flores',
        'owner_phone' => '09173334444',
        'owner_address' => 'Iloilo City',
        'status' => 'active',
    ]);

    $vaccination = $animal->vaccinations()->create([
        'vaccine_name' => 'Rabies',
        'given_on' => today()->subYear(),
        'next_due_on' => today()->addDays(2),
    ]);
    ScanLog::create([
        'animal_id' => $animal->id,
        'user_id' => $staff->id,
        'tag_identifier' => 'dashboard-tag',
        'result' => 'found',
        'location_text' => 'North gate',
        'scanned_at' => now(),
    ]);
    FeedingLog::create([
        'animal_id' => $animal->id,
        'feed_type' => 'Grains',
        'quantity' => 2,
        'unit' => 'kg',
        'fed_at' => now(),
    ]);
    Alert::create([
        'type' => 'vaccine_due_soon',
        'severity' => 'warning',
        'animal_id' => $animal->id,
        'title' => 'Rabies vaccine due soon',
        'message' => 'Rabies vaccination is due in two days.',
        'dedupe_key' => 'dashboard-vaccine-due-'.$animal->id,
        'status' => 'new',
        'triggered_at' => now(),
    ]);

    $this->actingAs($staff)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Vaccination attention')
        ->assertSee('Recent QR scans')
        ->assertSee('Latest alerts')
        ->assertSee('Rabies vaccine due soon')
        ->assertSee('North gate')
        ->assertViewHas('stats', fn ($stats) => $stats['feedings_today'] === 1 && $stats['open_alerts'] === 1)
        ->assertViewHas('upcomingVaccinations', fn ($vaccinations) => $vaccinations->contains('id', $vaccination->id));
});

test('animal registration filters species by category and saves category fields as attributes', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');
    $dog = Species::firstOrCreate(['name' => 'Dog', 'category' => 'companion']);
    $gamefowl = Species::firstOrCreate(['name' => 'Gamefowl', 'category' => 'gamefowl']);

    $this->actingAs($staff)
        ->get('/animals/create')
        ->assertOk()
        ->assertSee('Category')
        ->assertSee('Farm Livestock')
        ->assertSee('Gamefowl (Pang-sabong)')
        ->assertSee('Bloodline / strain')
        ->assertSee('Other (specify)');

    $invalidCategory = $this->actingAs($staff)->post('/animals', [
        'name' => 'Wrong category',
        'category' => 'gamefowl',
        'species_id' => $dog->id,
        'owner_name' => 'Tomas Reyes',
        'owner_phone' => '09174445555',
        'owner_address' => 'Davao City',
    ]);
    $invalidCategory->assertSessionHasErrors('species_id');

    $this->actingAs($staff)->post('/animals', [
        'name' => 'Black Tiger',
        'category' => 'gamefowl',
        'species_id' => $gamefowl->id,
        'breed' => 'Kelso',
        'sex' => 'male',
        'group_name' => 'North Farm',
        'quantity' => 1,
        'owner_name' => 'Tomas Reyes',
        'owner_phone' => '09174445555',
        'owner_address' => 'Davao City',
        'status' => 'active',
        'attributes' => [
            'age_class' => 'stag',
            'bloodline' => 'Kelso',
            'leg_band_no' => 'LB-104',
            'gamefarm_name' => 'North Farm',
        ],
    ])->assertRedirect('/animals');

    $animal = Animal::query()->where('name', 'Black Tiger')->firstOrFail();

    expect($animal->attributes['bloodline'])->toBe('Kelso');
    expect($animal->attributes['leg_band_no'])->toBe('LB-104');
    expect($animal->group_name)->toBe('North Farm');
    expect($animal->quantity)->toBe(1);

    $this->actingAs($staff)->get("/animals/{$animal->id}/edit")
        ->assertOk()
        ->assertSee('Gamefowl (Pang-sabong)')
        ->assertSee('LB-104');

    $this->actingAs($staff)->get("/animals/{$animal->id}")
        ->assertOk()
        ->assertSee('Bloodline / strain')
        ->assertSee('Kelso');

    $this->get('/t/'.$animal->tag->identifier)
        ->assertOk()
        ->assertSee('Gamefowl')
        ->assertSee('Bloodline / strain')
        ->assertSee('Kelso');

    $this->actingAs($staff)->get("/animals/{$animal->id}/print")
        ->assertOk()
        ->assertSee('Bloodline / strain')
        ->assertSee('Kelso');
});

test('staff can transfer ownership and keep history of previous owners', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $species = Species::factory()->create(['name' => 'Cow', 'category' => 'livestock']);
    $animal = Animal::create([
        'name' => 'Mila',
        'species_id' => $species->id,
        'sex' => 'female',
        'owner_name' => 'Alfred Dela Cruz',
        'owner_phone' => '09171110001',
        'owner_address' => 'Davao City',
        'status' => 'active',
    ]);

    $this->actingAs($staff)
        ->post("/animals/{$animal->id}/transfer", [
            'transfer_type' => 'sale',
            'from_owner_name' => 'Alfred Dela Cruz',
            'from_owner_phone' => '09171110001',
            'from_owner_address' => 'Davao City',
            'to_owner_name' => 'Belen Ramos',
            'to_owner_phone' => '09171234567',
            'to_owner_address' => 'General Santos City',
            'transferred_on' => '2026-10-07',
            'price' => '150000',
            'reference_no' => 'SALE-2026-001',
            'notes' => 'Transferred after sale agreement.',
        ])
        ->assertRedirect("/animals/{$animal->id}");

    $animal->refresh();

    expect($animal->owner_name)->toBe('Belen Ramos')
        ->and($animal->owner_phone)->toBe('09171234567')
        ->and($animal->owner_address)->toBe('General Santos City');

    $this->assertDatabaseHas('ownership_transfers', [
        'animal_id' => $animal->id,
        'to_owner_name' => 'Belen Ramos',
        'from_owner_name' => 'Alfred Dela Cruz',
        'reference_no' => 'SALE-2026-001',
    ]);

    $this->actingAs($staff)
        ->get("/animals/{$animal->id}")
        ->assertOk()
        ->assertSee('Ownership history')
        ->assertSee('Belen Ramos');
});
