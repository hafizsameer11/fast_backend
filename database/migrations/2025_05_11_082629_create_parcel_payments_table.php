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
        Schema::create('parcel_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcel_id')->constrained()->onDelete('cascade');
            $table->double('amount')->default(0);
            $table->string('payment_method')->default('wallet');
            $table->string('payment_status')->default('pending');
            $table->string('payment_reference')->nullable();
            $table->double('delivery_fee')->default(0);
            $table->boolean('is_pod')->default(false);
            $table->string('paying_user')->default('sender');
            $table->string('delivery_fee_status')->default('pending');
            $table->double('total_amount')->default(0);
            $table->foreign('parcel_id')->references('id')->on('send_parcels')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcel_payments');
    }
};
