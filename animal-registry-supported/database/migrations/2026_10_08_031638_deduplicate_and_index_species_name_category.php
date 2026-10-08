<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $duplicateGroups = DB::table('species')
                ->select('name', 'category')
                ->groupBy('name', 'category')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicateGroups as $duplicateGroup) {
                $ids = DB::table('species')
                    ->where('name', $duplicateGroup->name)
                    ->where('category', $duplicateGroup->category)
                    ->orderBy('id')
                    ->pluck('id');

                $retainedId = $ids->shift();
                $duplicateIds = $ids->all();

                if ($duplicateIds === []) {
                    continue;
                }

                DB::table('animals')->whereIn('species_id', $duplicateIds)->update(['species_id' => $retainedId]);
                DB::table('species')->whereIn('id', $duplicateIds)->delete();
            }
        });

        Schema::table('species', function (Blueprint $table): void {
            $table->unique(['name', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('species', function (Blueprint $table): void {
            $table->dropUnique('species_name_category_unique');
        });
    }
};
