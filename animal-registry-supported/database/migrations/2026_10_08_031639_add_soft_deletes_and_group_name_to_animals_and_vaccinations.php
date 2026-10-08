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
        Schema::table('animals', function (Blueprint $table): void {
            $table->string('group_name')->nullable()->index();
            $table->softDeletes();
        });

        Schema::table('vaccinations', function (Blueprint $table): void {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccinations', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('animals', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropIndex(['group_name']);
            $table->dropColumn('group_name');
        });
    }
};
