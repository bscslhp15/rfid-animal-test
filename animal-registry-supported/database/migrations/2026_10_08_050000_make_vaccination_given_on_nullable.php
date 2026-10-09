<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        if (Schema::hasColumn('vaccinations', 'given_on')) {
            Schema::table('vaccinations', function (Blueprint $table): void {
                $table->date('given_on')->nullable()->change();
            });
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

        if (Schema::hasColumn('vaccinations', 'given_on')) {
            Schema::table('vaccinations', function (Blueprint $table): void {
                $table->date('given_on')->nullable(false)->change();
            });
        }
    }
};
