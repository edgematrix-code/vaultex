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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('chain', 10);
            $table->string('type', 20); // deposit | withdrawal | internal
            $table->string('status', 20); // completed | pending | failed
            $table->decimal('amount', 36, 18);
            $table->decimal('usd_value', 24, 2)->default(0);
            $table->decimal('fee_usd', 24, 2)->default(0);
            $table->string('tx_hash', 128)->nullable();
            $table->string('from_address', 128)->nullable();
            $table->string('to_address', 128)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'chain']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
