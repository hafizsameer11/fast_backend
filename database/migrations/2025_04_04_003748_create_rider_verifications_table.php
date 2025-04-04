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
        Schema::create('rider_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
            // Step 1
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email_address');
            $table->string('phone');
            $table->string('address');
            $table->string('nin_number');
        
            // Step 2
            $table->string('vehicle_type')->nullable();
            $table->string('plate_number')->nullable();
            $table->string('riders_permit_number')->nullable();
            $table->string('color')->nullable();
            
            $table->string('passport_photo')->nullable();
            $table->string('rider_permit_upload')->nullable();
            $table->string('vehicle_video')->nullable();
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rider_verifications');
    }
};
