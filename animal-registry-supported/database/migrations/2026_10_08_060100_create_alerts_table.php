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
        Schema::create('alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('severity')->default('info');
            $table->foreignUuid('animal_id')->nullable()->constrained('animals')->nullOnDelete();
            $table->string('title');
            $table->text('message');
            $table->date('due_on')->nullable();
            $table->string('dedupe_key')->nullable()->unique();
            $table->string('status')->default('new');
            $table->timestamp('triggered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
