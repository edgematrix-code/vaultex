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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('chain', 10);
            $table->string('address', 128);
            $table->decimal('balance', 36, 18)->default(0);
            $table->decimal('price_usd', 24, 8)->default(0);
            $table->decimal('change_24h_pct', 12, 4)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'chain']);
            $table->index('chain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
