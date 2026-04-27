<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // customers tablosuna FK
            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->cascadeOnDelete();

            // CSV'yi kim yükledi
            $table->foreignId('uploaded_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // CSV'deki TransactionID (T1, T2 ...)
            $table->string('reference_number')->unique();

            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('account_balance_snapshot', 15, 2)->default(0);

            $table->date('transaction_date')->nullable();

            // CSV'de 143207 gibi HHMMSS formatında geliyor
            $table->string('transaction_time', 10)->nullable();

            $table->enum('type', ['income', 'expense', 'transfer', 'debit'])->default('debit');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');

            $table->string('category')->nullable();
            $table->string('description')->nullable();

            $table->timestamps();

            // Sık kullanılan sorgular için index
            $table->index('transaction_date');
            $table->index('customer_id');
            $table->index('uploaded_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};