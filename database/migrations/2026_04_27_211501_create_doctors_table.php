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
        Schema::create('doctors', function (Blueprint $table) {
            $table->foreignId('doctor_id')->constrained('users', 'user_id')->cascadeOnDelete()->cascadeOnUpdate()->primary();
            $table->enum('specialization', [
                'cardiology',
                'dermatology',
                'neurology',
                'pediatrics',
                'orthopedics',
                'ophthalmology'
            ]);
            $table->string('license_number');
            $table->string('phone');
            $table->index('specialization');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
