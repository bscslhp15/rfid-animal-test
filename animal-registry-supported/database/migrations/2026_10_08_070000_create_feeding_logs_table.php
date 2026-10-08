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
        Schema::create('feeding_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('animal_id')->constrained('animals')->cascadeOnDelete();
            $table->string('feed_type');
            $table->decimal('quantity', 8, 2);
            $table->string('unit')->default('kg');
            $table->dateTime('fed_at');
            $table->decimal('cost', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feeding_logs');
    }
};
