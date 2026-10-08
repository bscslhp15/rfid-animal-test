<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->uuid('animal_id');
            $table->string('identifier')->unique();
            $table->string('type')->default('qr');
            $table->string('status')->default('active');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->string('deactivation_reason')->nullable();
            $table->timestamps();

            $table->foreign('animal_id')->references('id')->on('animals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
