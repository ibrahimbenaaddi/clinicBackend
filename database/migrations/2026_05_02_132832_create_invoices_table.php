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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->foreignId('appointment_id')->constrained('appointments', 'appointment_id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->double('amount');
            $table->dateTime('invoice_date');
            $table->enum('status', [
                'pending',
                'paid',
                'cancelled',
                'refunded',
                'overdue'
            ])->default('pending');
            $table->enum('payment_method', [
                'cash',
                'card',
                'insurance',
                'bank_transfer'
            ]);
            $table->index('status');
            $table->index('payment_method');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
