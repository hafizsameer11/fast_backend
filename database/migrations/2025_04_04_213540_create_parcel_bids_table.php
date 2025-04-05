<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parcel_bids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('send_parcel_id');
            $table->unsignedBigInteger('rider_id')->nullable(); // Nullable if user created
            $table->unsignedBigInteger('user_id')->nullable(); // Nullable if rider created
            $table->decimal('bid_amount', 10, 2)->nullable(); // Optional custom offer
            $table->string('message')->nullable(); // Optional message or comment
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->enum('created_by', ['rider', 'user'])->default('rider');
            $table->timestamps();

            $table->foreign('send_parcel_id')->references('id')->on('send_parcels')->onDelete('cascade');
            $table->foreign('rider_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcel_bids');
    }
};
