<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('support_chats', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->enum('sender_type', ['user', 'rider', 'admin']);
            $table->enum('receiver_type', ['user', 'rider', 'admin'])->nullable();
            $table->text('message');
            $table->enum('status', ['pending', 'replied'])->default('pending'); // This can be 'pending' or 'replied'
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_chats');
    }
};
