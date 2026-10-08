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
        if (! Schema::hasTable('vaccinations')) {
            return;
        }

        $columns = DB::select("PRAGMA table_info('vaccinations')");
        $givenOnColumn = collect($columns)->firstWhere('name', 'given_on');

        if ($givenOnColumn && $givenOnColumn->notnull === 1) {
            Schema::create('vaccinations_new', function ($table) {
                $table->uuid('id')->primary();
                $table->uuid('animal_id');
                $table->string('vaccine_name');
                $table->date('given_on')->nullable();
                $table->date('next_due_on')->nullable();
                $table->string('batch_no')->nullable();
                $table->string('administered_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('animal_id')->references('id')->on('animals')->cascadeOnDelete();
            });

            DB::statement('INSERT INTO vaccinations_new (id, animal_id, vaccine_name, given_on, next_due_on, batch_no, administered_by, notes, created_at, updated_at, deleted_at) SELECT id, animal_id, vaccine_name, given_on, next_due_on, batch_no, administered_by, notes, created_at, updated_at, deleted_at FROM vaccinations');

            Schema::drop('vaccinations');
            Schema::rename('vaccinations_new', 'vaccinations');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('vaccinations')) {
            return;
        }

        $columns = DB::select("PRAGMA table_info('vaccinations')");
        $givenOnColumn = collect($columns)->firstWhere('name', 'given_on');

        if ($givenOnColumn && $givenOnColumn->notnull === 0) {
            Schema::create('vaccinations_new', function ($table) {
                $table->uuid('id')->primary();
                $table->uuid('animal_id');
                $table->string('vaccine_name');
                $table->date('given_on');
                $table->date('next_due_on')->nullable();
                $table->string('batch_no')->nullable();
                $table->string('administered_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('animal_id')->references('id')->on('animals')->cascadeOnDelete();
            });

            DB::statement('INSERT INTO vaccinations_new (id, animal_id, vaccine_name, given_on, next_due_on, batch_no, administered_by, notes, created_at, updated_at, deleted_at) SELECT id, animal_id, vaccine_name, given_on, next_due_on, batch_no, administered_by, notes, created_at, updated_at, deleted_at FROM vaccinations');

            Schema::drop('vaccinations');
            Schema::rename('vaccinations_new', 'vaccinations');
        }
    }
};
