<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Örnek gelirler
        $incomes = [
            ['description' => 'Maaş - Ocak', 'amount' => 45000, 'category' => 'salary'],
            ['description' => 'Freelance Proje', 'amount' => 12000, 'category' => 'income'],
            ['description' => 'Maaş - Şubat', 'amount' => 45000, 'category' => 'salary'],
        ];

        foreach ($incomes as $i => $income) {
            Transaction::create([
                'user_id'          => 1,
                'amount'           => $income['amount'],
                'description'      => $income['description'],
                'category'         => $income['category'],
                'transaction_date' => now()->subMonths(2)->addDays($i * 15)->format('Y-m-d'),
                'transaction_type' => 'credit',
                'source'           => 'manual',
            ]);
        }

        // Örnek giderler
        $expenses = [
            ['desc' => 'Restoran - Pizza', 'amount' => 450, 'cat' => 'food', 'loc' => 'Mumbai'],
            ['desc' => 'Uber Yolculuğu', 'amount' => 280, 'cat' => 'transport', 'loc' => 'Mumbai'],
            ['desc' => 'Amazon Alışverişi', 'amount' => 3200, 'cat' => 'shopping', 'loc' => 'Online'],
            ['desc' => 'Elektrik Faturası', 'amount' => 1800, 'cat' => 'utilities', 'loc' => 'Mumbai'],
            ['desc' => 'Netflix', 'amount' => 649, 'cat' => 'entertainment', 'loc' => 'Online'],
            ['desc' => 'Eczane', 'amount' => 520, 'cat' => 'medical', 'loc' => 'Delhi'],
            ['desc' => 'Swiggy Sipariş', 'amount' => 380, 'cat' => 'food', 'loc' => 'Mumbai'],
            ['desc' => 'Metro Kart', 'amount' => 200, 'cat' => 'transport', 'loc' => 'Delhi'],
            ['desc' => 'Kitap Alışverişi', 'amount' => 950, 'cat' => 'education', 'loc' => 'Mumbai'],
            ['desc' => 'ATM Çekimi', 'amount' => 5000, 'cat' => 'atm', 'loc' => 'Pune'],
            ['desc' => 'İnternet Faturası', 'amount' => 899, 'cat' => 'utilities', 'loc' => 'Mumbai'],
            ['desc' => 'Sinema Bileti', 'amount' => 450, 'cat' => 'entertainment', 'loc' => 'Mumbai'],
            ['desc' => 'Zomato Sipariş', 'amount' => 620, 'cat' => 'food', 'loc' => 'Delhi'],
            ['desc' => 'Araba Yıkama', 'amount' => 300, 'cat' => 'transport', 'loc' => 'Pune'],
            ['desc' => 'Flipkart Sipariş', 'amount' => 2100, 'cat' => 'shopping', 'loc' => 'Online'],
        ];

        foreach ($expenses as $i => $exp) {
            Transaction::create([
                'user_id'          => 1,
                'amount'           => -abs($exp['amount']),
                'description'      => $exp['desc'],
                'category'         => $exp['cat'],
                'location'         => $exp['loc'],
                'transaction_date' => now()->subDays(rand(1, 60))->format('Y-m-d'),
                'transaction_type' => 'debit',
                'source'           => 'manual',
            ]);
        }

        $this->command->info('✓ Örnek veriler oluşturuldu: ' . (count($incomes) + count($expenses)) . ' işlem');
    }
}
