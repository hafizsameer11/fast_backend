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
        Schema::table('send_parcels', function (Blueprint $table) {
            $table->string('sender_lat')->nullable();
            $table->string('sender_long')->nullable();
            $table->string('receiver_lat')->nullable();
            $table->string('receiver_long')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('send_parcels', function (Blueprint $table) {
            //
        });
    }
};
