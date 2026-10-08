<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('animal_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tag_identifier')->nullable();
            $table->string('result')->default('found');
            $table->string('location_text')->nullable();
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamps();

            $table->foreign('animal_id')->references('id')->on('animals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_logs');
    }
};
