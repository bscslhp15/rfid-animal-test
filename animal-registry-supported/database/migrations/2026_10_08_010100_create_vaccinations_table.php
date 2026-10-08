<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->string('vaccine_name');
            $table->date('given_on')->nullable();
            $table->date('next_due_on')->nullable();
            $table->string('batch_no')->nullable();
            $table->string('administered_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('animal_id')->references('id')->on('animals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccinations');
    }
};
