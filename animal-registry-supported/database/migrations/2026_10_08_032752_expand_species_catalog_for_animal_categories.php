<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $rooster = DB::table('species')->where('name', 'Rooster')->where('category', 'gamefowl')->first();
        $gamefowl = DB::table('species')->where('name', 'Gamefowl')->where('category', 'gamefowl')->first();

        if ($rooster && $gamefowl) {
            DB::table('animals')->where('species_id', $rooster->id)->update(['species_id' => $gamefowl->id]);
            DB::table('species')->where('id', $rooster->id)->delete();
        } elseif ($rooster) {
            DB::table('species')->where('id', $rooster->id)->update(['name' => 'Gamefowl', 'updated_at' => now()]);
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep added catalog rows so registrations using them remain valid after rollback.
    }
};
