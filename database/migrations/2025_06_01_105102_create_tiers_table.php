<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tiers', function (Blueprint $table) {
            $table->id();
            $table->integer('tier'); // like 1, 2, 3...
            $table->integer('no_of_rides');
            $table->decimal('commission', 8, 2); // percentage or amount
            $table->decimal('tier_amount', 10, 2);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tiers');
    }
};