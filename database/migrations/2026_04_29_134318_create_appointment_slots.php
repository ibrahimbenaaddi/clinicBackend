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
        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id('slot_id');
            $table->foreignId('doctor_id')->constrained('doctors', 'doctor_id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('booked_count')->default(0);
            $table->integer('max_patients');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('status', [
                'available',
                'full',
                'blocked',
                'cancelled',
            ])->default('available');
            $table->index(['doctor_id', 'status']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_slots');
    }
};
