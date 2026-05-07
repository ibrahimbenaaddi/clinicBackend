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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('appointment_id');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('doctor_id')->constrained('doctors', 'doctor_id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('slot_id')->constrained('appointment_slots', 'slot_id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('status', [
                'pending',
                'confirmed',
                'completed',
                'cancelled',
                'no_show'
            ])->default('pending');
            $table->text('reason_for_visit');
            $table->index('start_time');
            $table->index('end_time');
            $table->index('status');
            $table->unique(['slot_id', 'patient_id'], 'unique_patient_per_slot');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
