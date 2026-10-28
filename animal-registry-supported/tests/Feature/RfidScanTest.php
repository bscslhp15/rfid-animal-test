<?php

use App\Models\Animal;
use App\Models\Species;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('a registered RFID scan redirects to the matching animal profile', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');
    $species = Species::factory()->create(['name' => 'Dog']);
    $animal = Animal::create([
        'name' => 'RFID Rex',
        'rfid_tag_id' => '901021000338099',
        'species_id' => $species->id,
        'owner_name' => 'Maria Santos',
        'owner_phone' => '09171234567',
        'owner_address' => 'Davao City',
        'status' => 'active',
    ]);

    $this->actingAs($staff)
        ->get('/rfid/scan')
        ->assertOk()
        ->assertSee('Scan RFID Tag')
        ->assertSee('requestSubmit', false);

    $this->actingAs($staff)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('RFID ready · scan a tag')
        ->assertSee('rfid/scan', false)
        ->assertSee("event.key === 'Tab'", false);

    $this->actingAs($staff)
        ->post('/rfid/scan', ['rfid_tag_id' => ' 901021000338099 '])
        ->assertRedirect("/animals/{$animal->id}");

    $this->actingAs($staff)
        ->postJson('/rfid/scan', ['rfid_tag_id' => '901021000338099'])
        ->assertOk()
        ->assertJsonPath('redirect', route('animals.show', $animal));
});

test('a public RFID scan works while logged out and opens the public animal QR record', function () {
    $species = Species::factory()->create(['name' => 'Dog']);
    $animal = Animal::create([
        'name' => 'Public RFID Rex',
        'rfid_tag_id' => '901021000338099',
        'species_id' => $species->id,
        'owner_name' => 'Maria Santos',
        'owner_phone' => '09171234567',
        'owner_address' => 'Davao City',
        'status' => 'active',
    ]);
    $animal->tag()->create([
        'identifier' => 'public-rfid-token',
        'type' => 'qr',
        'status' => 'active',
        'assigned_at' => now(),
    ]);
    Role::firstOrCreate(['name' => 'staff']);
    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $this->get('/')
        ->assertOk()
        ->assertSee('rfid/public-scan', false)
        ->assertSee('RFID ready · scan a tag');

    $this->get('/login')
        ->assertOk()
        ->assertSee('rfid/public-scan', false)
        ->assertSee('RFID ready · scan a tag');

    $this->actingAs($staff)
        ->get('/')
        ->assertOk()
        ->assertSee('rfid/public-scan', false);

    $this->actingAsGuest();

    $this->postJson('/rfid/public-scan', ['rfid_tag_id' => ' 901021000338099 '])
        ->assertOk()
        ->assertJsonPath('redirect', route('animal.public', 'public-rfid-token'));

    $this->actingAs($staff)
        ->postJson('/rfid/public-scan', ['rfid_tag_id' => '901021000338099'])
        ->assertOk()
        ->assertJsonPath('redirect', route('animals.show', $animal));
});

test('public RFID scan does not reveal a record without an active QR tag', function () {
    $species = Species::factory()->create(['name' => 'Dog']);
    Animal::create([
        'name' => 'No QR Rex',
        'rfid_tag_id' => '901021000338099',
        'species_id' => $species->id,
        'owner_name' => 'Maria Santos',
        'owner_phone' => '09171234567',
        'owner_address' => 'Davao City',
        'status' => 'active',
    ]);

    $this->postJson('/rfid/public-scan', ['rfid_tag_id' => '901021000338099'])
        ->assertNotFound()
        ->assertJsonPath('message', 'No public animal record is registered with that RFID tag.');
});

test('an unknown RFID scan stays on the scan page and reports an error', function () {
    Role::firstOrCreate(['name' => 'staff']);

    $staff = User::factory()->create(['email_verified_at' => now()]);
    $staff->assignRole('staff');

    $this->actingAs($staff)
        ->from('/rfid/scan')
        ->post('/rfid/scan', ['rfid_tag_id' => '000000000000000'])
        ->assertRedirect('/rfid/scan')
        ->assertSessionHasErrors('rfid_tag_id');

    $this->actingAs($staff)
        ->postJson('/rfid/scan', ['rfid_tag_id' => '000000000000000'])
        ->assertNotFound()
        ->assertJsonPath('message', 'No animal is registered with that RFID tag.');
});

test('RFID scan lookup requires an authorized signed-in registry user', function () {
    Role::firstOrCreate(['name' => 'owner']);

    $owner = User::factory()->create(['email_verified_at' => now()]);
    $owner->assignRole('owner');

    $this->get('/rfid/scan')->assertRedirect('/login');

    $this->actingAs($owner)
        ->get('/rfid/scan')
        ->assertForbidden();

    $this->actingAs($owner)
        ->post('/rfid/scan', ['rfid_tag_id' => '901021000338099'])
        ->assertForbidden();
});
