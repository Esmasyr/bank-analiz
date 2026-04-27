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
            $table->unsignedBigInteger('user_id')->default(1);
            $table->decimal('amount', 15, 2); // negatif=gider, pozitif=gelir
            $table->string('category')->nullable();
            $table->date('transaction_date');
            $table->string('description')->nullable();
            $table->string('location')->nullable();
            $table->string('transaction_type')->default('debit'); // debit/credit
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->string('source')->default('manual'); // manual, csv, api
            $table->string('external_id')->nullable()->unique(); // CSV'den gelen ID
            $table->timestamps();

            $table->index(['user_id', 'transaction_date']);
            $table->index('category');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color')->default('#6366f1');
            $table->string('icon')->default('💰');
            $table->string('type')->default('expense'); // expense/income
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('categories');
    }
};
