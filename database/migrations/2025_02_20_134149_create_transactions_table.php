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
            $table->unsignedBigInteger('wallet_id'); // Reference to the wallet
            $table->enum('type', ['deposit', 'withdrawal', 'transfer_in', 'transfer_out']); // Type of transaction
            $table->decimal('amount', 15, 2); // Amount involved
            $table->text('description')->nullable(); // Transaction description (e.g., 'Deposit', 'Withdrawal', etc.)
            $table->timestamps();
    
            // Foreign key relationship to wallets table
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
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
