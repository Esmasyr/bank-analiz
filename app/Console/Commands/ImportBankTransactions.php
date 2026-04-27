<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportBankTransactions extends Command
{
    protected $signature = 'import:bank {file : CSV dosyasının yolu}';
    protected $description = 'Banka işlemlerini CSV dosyasından içe aktar';

    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("Dosya bulunamadı: $file");
            return 1;
        }

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // ilk satır başlık

        $userId = auth()->id() ?? 1;
        $imported = 0;
        $skipped = 0;

        $this->info('Import başlıyor...');
        $bar = $this->output->createProgressBar();
        $bar->start();

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            // Müşteriyi bul veya oluştur
            $customer = DB::table('customers')->where('name', $data['CustomerID'] ?? null)->first();

            if (!$customer) {
                $customerId = DB::table('customers')->insertGetId([
                    'uploaded_by_user_id'  => $userId,
                    'name'                 => $data['CustomerID'] ?? 'Bilinmiyor',
                    'gender'               => $data['CustGender'] ?? null,
                    'location'             => $data['CustLocation'] ?? null,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            } else {
                $customerId = $customer->id;
            }

            // Aynı referans numarası varsa atla
            $exists = DB::table('transactions')->where('reference_number', $data['TransactionID'] ?? null)->exists();
            if ($exists) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // Tarihi parse et
            $date = null;
            if (!empty($data['TransactionDate'])) {
                try {
                    $date = Carbon::parse($data['TransactionDate'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $date = null;
                }
            }

            DB::table('transactions')->insert([
                'user_id' => $userId,
                'uploaded_by_user_id'      => $userId,
                'reference_number'         => $data['TransactionID'] ?? null,
                'amount'                   => $data['TransactionAmount (INR)'] ?? 0,
                'processed_at'             => $date,
                'type'                     => 'deposit',
                'status'                   => 'completed',
                'description'              => ($data['CustLocation'] ?? '') . ' - ' . ($data['CustGender'] ?? ''),
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);

            $imported++;
            $bar->advance();
        }

        $bar->finish();
        fclose($handle);

        $this->newLine();
        $this->info("✅ Import tamamlandı! $imported kayıt eklendi, $skipped atlandı.");

        return 0;
    }
}
