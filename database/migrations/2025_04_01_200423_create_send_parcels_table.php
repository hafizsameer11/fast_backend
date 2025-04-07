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

            // Step 1
            $table->string('sender_address');
            $table->string('receiver_address');

            $table->enum('schedule_type', ['immediate', 'scheduled'])->default('immediate');
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();

            // Step 2
            $table->string('sender_name')->nullable();
            $table->string('sender_phone')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();

            // Step 3
            $table->string('parcel_name')->nullable();
            $table->string('parcel_category')->nullable();
            $table->decimal('parcel_value', 10, 2)->nullable();
            $table->text('description')->nullable();

            // Step 4
            $table->string('payer')->nullable(); // sender, receiver, third-party
            $table->enum('payment_method', ['wallet', 'bank'])->nullable();
            $table->enum('pay_on_delivery', ['yes', 'no'])->default('no');
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();

            // Tracking + status
            $table->enum('status', ['draft', 'ordered', 'picked_up', 'in_transit', 'delivered'])->default('draft');
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->unsignedBigInteger('rider_id')->nullable();
            $table->unsignedBigInteger('accepted_bid_id')->nullable();
            $table->boolean('is_assigned')->default(false);

            $table->string('pickup_code', 4)->nullable();
            $table->string('delivery_code', 4)->nullable();
            $table->string('is_pickup_confirmed')->default('no');
            $table->string('is_delivery_confirmed')->default('no');

            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('send_parcels');
    }
};
