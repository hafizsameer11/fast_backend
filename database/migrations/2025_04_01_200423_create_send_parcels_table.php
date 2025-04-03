<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('send_parcels', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->string('sender_address');
            $table->string('receiver_address');
            $table->string('sender_name');
            $table->string('sender_phone');
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('parcel_category');
            $table->decimal('parcel_value', 10, 2);
            $table->text('description')->nullable();
            $table->string('payer'); // sender, receiver, third-party
            $table->decimal('amount', 10, 2);
            $table->decimal('delivery_fee', 10, 2);

            // Status + timestamps for each phase
            $table->enum('status', ['ordered', 'picked_up', 'in_transit', 'delivered'])->default('ordered');
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('send_parcels');
    }
};

