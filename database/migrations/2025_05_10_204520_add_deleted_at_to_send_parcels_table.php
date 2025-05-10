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
            $table->boolean('is_canceled')->default(false)->after('status');
            $table->string('cancellation_reason')->nullable()->after('is_canceled');
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
