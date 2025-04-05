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
        Schema::create('parcel_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('send_parcel_id');
            $table->unsignedBigInteger('from_user_id'); // who reviewed
            $table->unsignedBigInteger('to_user_id');   // who was reviewed
            $table->tinyInteger('rating'); // 1-5
            $table->text('review');
            $table->timestamps();

            $table->foreign('send_parcel_id')->references('id')->on('send_parcels')->onDelete('cascade');
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcel_reviews');
    }
};
