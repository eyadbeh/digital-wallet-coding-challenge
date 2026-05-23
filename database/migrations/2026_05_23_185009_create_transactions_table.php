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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('reference');
            $table->decimal('amount', 15, 2);
            $table->timestamp('transaction_date');
            $table->text('note')->nullable();
            $table->string('internal_reference')->nullable();
            $table->longText('raw_payload')->nullable();
            $table->timestamps();
            $table->unique(['bank_name', 'reference'], 'idx_transaction_unique');
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
