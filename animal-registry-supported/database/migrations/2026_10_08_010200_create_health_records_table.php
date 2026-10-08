<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->string('type')->default('temperature');
            $table->decimal('value_numeric', 8, 2);
            $table->string('unit')->nullable();
            $table->text('details')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->string('recorded_by')->nullable();
            $table->timestamps();

            $table->foreign('animal_id')->references('id')->on('animals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
