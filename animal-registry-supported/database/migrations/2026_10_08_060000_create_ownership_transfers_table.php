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
        Schema::create('ownership_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('animal_id')->constrained('animals')->cascadeOnDelete();
            $table->string('from_owner_name');
            $table->string('from_owner_phone')->nullable();
            $table->text('from_owner_address')->nullable();
            $table->string('to_owner_name');
            $table->string('to_owner_phone')->nullable();
            $table->text('to_owner_address')->nullable();
            $table->string('transfer_type')->default('sale');
            $table->date('transferred_on');
            $table->decimal('price', 12, 2)->nullable();
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ownership_transfers');
    }
};
