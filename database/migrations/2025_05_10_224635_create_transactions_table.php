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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            // $table->unsignedBigInteger('virtual_account_id')->nullable();
            $table->string('transaction_type')->nullable(); // e.g., 'credit', 'debit'
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('status')->nullable(); // e.g., 'pending', 'completed', 'failed'
            $table->string('reference')->nullable(); // Unique reference for the transaction
            $table->string('icon')->nullable(); // Icon for the transaction
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
