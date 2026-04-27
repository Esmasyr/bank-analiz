<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function analytics()
    {
        $userId = auth()->id();

        $stats = [
            'total_transactions' => Transaction::where('uploaded_by_user_id', $userId)->count(),
            'total_customers'    => Customer::where('uploaded_by_user_id', $userId)->count(),
            'total_amount'       => Transaction::where('uploaded_by_user_id', $userId)->sum('amount'),
            'avg_amount'         => Transaction::where('uploaded_by_user_id', $userId)->avg('amount'),
            'max_amount'         => Transaction::where('uploaded_by_user_id', $userId)->max('amount'),
        ];

        $monthlyData = Transaction::where('uploaded_by_user_id', $userId)
            ->whereNotNull('processed_at')
            ->select(
                DB::raw("DATE_FORMAT(processed_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total'),
                DB::raw('AVG(amount) as average')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $locationData = Customer::where('uploaded_by_user_id', $userId)
            ->select('location', DB::raw('COUNT(*) as count'), DB::raw('AVG(account_balance) as avg_balance'))
            ->groupBy('location')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $genderData = Customer::where('uploaded_by_user_id', $userId)
            ->select('gender', DB::raw('COUNT(*) as count'))
            ->groupBy('gender')
            ->get()
            ->pluck('count', 'gender');

        $balanceRanges = $this->getBalanceRanges($userId);

        $lastImport = Transaction::where('uploaded_by_user_id', $userId)
            ->latest()
            ->value('created_at');

        return view('analytics', compact(
            'stats',
            'monthlyData',
            'locationData',
            'genderData',
            'balanceRanges',
            'lastImport'
        ));
    }

    public function csvImportPage()
    {
        return view('transactions.csv-import');
    }

    public function csvImport(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:102400'],
        ]);

        $file   = $request->file('csv_file');
        $path   = $file->storeAs('imports', 'bank_' . now()->format('YmdHis') . '.csv');
        $userId = auth()->id();

        $lineCount = $this->estimateLines(storage_path('app/' . $path));

        if ($lineCount <= 100000) {
            $this->processImport(storage_path('app/' . $path), $userId);

            return redirect()->route('analytics')
                ->with('success', "✅ CSV başarıyla içe aktarıldı!");
        }

        $command = "php artisan import:bank-transactions "
            . escapeshellarg(storage_path('app/' . $path))
            . " --user={$userId}";

        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen("start /B {$command}", 'r'));
        } else {
            exec("{$command} > /dev/null 2>&1 &");
        }

        return redirect()->route('analytics')
            ->with('info', "⏳ Büyük dosya arka planda içe aktarılıyor ({$lineCount} satır). Birkaç dakika sonra yenileyiniz.");
    }

    public static function getDashboardStats(int $userId): array
    {
        $q = Transaction::where('uploaded_by_user_id', $userId);

        return [
            'balance'        => (clone $q)->where('status', 'completed')->sum('amount'),
            'total_income'   => (clone $q)->where('type', 'deposit')->where('status', 'completed')->sum('amount'),
            'total_expense'  => (clone $q)->where('type', 'withdrawal')->where('status', 'completed')->sum('amount'),
            'total_transfer' => (clone $q)->where('type', 'transfer')->where('status', 'completed')->sum('amount'),
            'pending'        => (clone $q)->where('status', 'pending')->count(),
            'completed'      => (clone $q)->where('status', 'completed')->count(),
            'failed'         => (clone $q)->where('status', 'failed')->count(),
        ];
    }

    public static function getMonthlyData(int $userId): array
    {
        return Transaction::where('uploaded_by_user_id', $userId)
            ->whereNotNull('processed_at')
            ->select(
                DB::raw("DATE_FORMAT(processed_at, '%b %Y') as month"),
                DB::raw("SUM(CASE WHEN type='deposit' THEN amount ELSE 0 END) as income"),
                DB::raw("SUM(CASE WHEN type IN ('withdrawal','transfer') THEN amount ELSE 0 END) as expense")
            )
            ->groupBy(DB::raw("DATE_FORMAT(processed_at, '%b %Y')"), DB::raw("DATE_FORMAT(processed_at, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(processed_at, '%Y-%m')"))
            ->get()
            ->toArray();
    }

    private function processImport(string $filePath, int $userId): void
    {
        $handle    = fopen($filePath, 'r');
        fgetcsv($handle);

        $customers    = [];
        $transactions = [];
        $row          = 0;
        $chunkSize    = 1000;

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 9) continue;

            [
                $transactionId,
                $customerId,
                $dob,
                $gender,
                $location,
                $balance,
                $txDate,
                $txTime,
                $amount,
            ] = $data;

            $customerId    = trim($customerId);
            $transactionId = trim($transactionId);

            if (! isset($customers[$customerId])) {
                $customers[$customerId] = [
                    'customer_id'         => $customerId,
                    'date_of_birth'       => $this->parseDate($dob),
                    'gender'              => in_array(trim($gender), ['M', 'F', 'T']) ? trim($gender) : 'other',
                    'location'            => trim($location),
                    'account_balance'     => is_numeric($balance) ? (float) $balance : 0,
                    'uploaded_by_user_id' => $userId,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
            }

            $transactions[] = [
                'reference_number'         => $transactionId,
                'customer_id'              => $customerId,
                'amount'                   => is_numeric($amount) ? (float) $amount : 0,
                'account_balance_snapshot' => is_numeric($balance) ? (float) $balance : 0,
                'processed_at'             => $this->parseDate($txDate),
                'transaction_time'         => $this->formatTime(trim($txTime)),
                'type'                     => 'debit',
                'status'                   => 'completed',
                'user_id'                  => $userId,  // ← eklendi
                'uploaded_by_user_id'      => $userId,
                'created_at'               => now(),
                'updated_at'               => now(),
            ];

            $row++;

            if ($row % $chunkSize === 0) {
                $this->flushChunk($customers, $transactions, $userId);
                $customers    = [];
                $transactions = [];
            }
        }

        if (! empty($transactions)) {
            $this->flushChunk($customers, $transactions, $userId);
        }

        fclose($handle);
    }

    private function flushChunk(array $customers, array $transactions, int $userId): void
    {
        Customer::upsert(
            array_values($customers),
            ['customer_id'],
            ['account_balance', 'updated_at']
        );

        $customerIds = array_column($customers, 'customer_id');
        $map = Customer::whereIn('customer_id', $customerIds)->pluck('id', 'customer_id');

        $mapped = array_values(array_filter(array_map(function ($t) use ($map) {
            $id = $map[$t['customer_id']] ?? null;
            if (! $id) return null;
            $t['customer_id'] = $id;
            return $t;
        }, $transactions)));

        if (! empty($mapped)) {
            Transaction::upsert(
                $mapped,
                ['reference_number'],
                ['amount', 'account_balance_snapshot', 'updated_at']
            );
        }
    }

    private function getBalanceRanges(int $userId): array
    {
        return Customer::where('uploaded_by_user_id', $userId)
            ->select(DB::raw("
                CASE
                    WHEN account_balance < 1000    THEN '< 1K'
                    WHEN account_balance < 10000   THEN '1K - 10K'
                    WHEN account_balance < 100000  THEN '10K - 100K'
                    WHEN account_balance < 1000000 THEN '100K - 1M'
                    ELSE '> 1M'
                END as balance_range
            "), DB::raw('COUNT(*) as count'))
            ->groupBy('balance_range')
            ->get()
            ->pluck('count', 'balance_range')
            ->toArray();
    }

    private function parseDate(string $raw): ?string
    {
        $raw = trim($raw);
        if (empty($raw)) return null;

        foreach (['d/m/y', 'd/m/Y', 'm/d/y', 'm/d/Y'] as $format) {
            try {
                $parsed = \Carbon\Carbon::createFromFormat($format, $raw);
                if ($parsed && $parsed->year >= 1900) {
                    return $parsed->toDateString();
                }
            } catch (\Exception) {}
        }

        return null;
    }

    private function formatTime(string $raw): ?string
    {
        $raw = trim($raw);
        if (empty($raw)) return null;
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $raw)) return $raw;

        $padded = str_pad($raw, 6, '0', STR_PAD_LEFT);
        return substr($padded, 0, 2) . ':' . substr($padded, 2, 2) . ':' . substr($padded, 4, 2);
    }

    private function estimateLines(string $file): int
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return (int) shell_exec("wc -l < " . escapeshellarg($file));
        }
        $count  = 0;
        $handle = fopen($file, 'r');
        while (! feof($handle)) { fgets($handle); $count++; }
        fclose($handle);
        return $count;
    }
}