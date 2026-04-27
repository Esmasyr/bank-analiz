<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Analiz Servisi
 * Öğrenilen kavramlar: group by, ortalama, trend, istatistik
 */
class AnalysisService
{
    /**
     * SEVIYE 3: Temel toplamlar
     */
    public function getSummary(int $userId = 1, ?string $period = null): array
    {
        $query = Transaction::where('user_id', $userId);

        if ($period) {
            [$start, $end] = $this->parsePeriod($period);
            $query->inPeriod($start, $end);
        }

        $totalIncome   = (clone $query)->income()->sum('amount');
        $totalExpense  = abs((clone $query)->expenses()->sum('amount'));
        $netBalance    = $totalIncome - $totalExpense;
        $txCount       = $query->count();
        $avgTx         = $txCount > 0 ? ($totalExpense / $txCount) : 0;

        return [
            'total_income'    => round($totalIncome, 2),
            'total_expense'   => round($totalExpense, 2),
            'net_balance'     => round($netBalance, 2),
            'tx_count'        => $txCount,
            'avg_transaction' => round($avgTx, 2),
            'savings_rate'    => $totalIncome > 0 ? round((($totalIncome - $totalExpense) / $totalIncome) * 100, 1) : 0,
        ];
    }

    /**
     * SEVIYE 2: Kategoriye göre gruplama
     * Kavram: GROUP BY + SUM + COUNT
     */
    public function getByCategory(int $userId = 1, ?string $period = null): Collection
    {
        $query = Transaction::where('user_id', $userId)
            ->expenses()
            ->whereNotNull('category')
            ->select(
                'category',
                DB::raw('SUM(ABS(amount)) as total'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(ABS(amount)) as avg_amount'),
                DB::raw('MAX(ABS(amount)) as max_amount')
            )
            ->groupBy('category')
            ->orderByDesc('total');

        if ($period) {
            [$start, $end] = $this->parsePeriod($period);
            $query->inPeriod($start, $end);
        }

        $results = $query->get();
        $grandTotal = $results->sum('total');

        return $results->map(function ($item) use ($grandTotal) {
            $item->percentage = $grandTotal > 0 ? round(($item->total / $grandTotal) * 100, 1) : 0;
            return $item;
        });
    }

    /**
     * SEVIYE 3: Aylık trend analizi
     * Kavram: GROUP BY month, trend hesaplama
     */
    public function getMonthlyTrend(int $userId = 1, int $months = 12): array
    {
        $data = Transaction::where('user_id', $userId)
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as month"),
                DB::raw('SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as expense'),
                DB::raw('COUNT(*) as count')
            )
            ->where('transaction_date', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Trend hesapla (son ay - önceki ay / önceki ay)
        $trend = null;
        if ($data->count() >= 2) {
            $last   = $data->last()->expense;
            $prev   = $data->nth(-1, 1)?->expense ?? 1;
            $trend  = $prev > 0 ? round((($last - $prev) / $prev) * 100, 1) : 0;
        }

        return [
            'data'  => $data,
            'trend' => $trend,
        ];
    }

    /**
     * SEVIYE 4: Haftalık harcama dağılımı
     */
    public function getWeeklyPattern(int $userId = 1): Collection
    {
        return Transaction::where('user_id', $userId)
            ->expenses()
            ->select(
                DB::raw('DAYOFWEEK(transaction_date) as day_of_week'),
                DB::raw('DAYNAME(transaction_date) as day_name'),
                DB::raw('AVG(ABS(amount)) as avg_amount'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('day_of_week', 'day_name')
            ->orderBy('day_of_week')
            ->get();
    }

    /**
     * SEVIYE 5: Basit tahmin - Son 3 ayın ortalamasını kullan
     * Kavram: Moving average, basit lineer regression
     */
    public function getForecast(int $userId = 1): array
    {
        $monthlyData = Transaction::where('user_id', $userId)
            ->expenses()
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as month"),
                DB::raw('SUM(ABS(amount)) as total')
            )
            ->where('transaction_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        if (count($monthlyData) < 2) {
            return ['forecast' => 0, 'confidence' => 'low', 'method' => 'insufficient_data'];
        }

        $values = array_values($monthlyData);

        // Moving average (son 3 ay)
        $window     = min(3, count($values));
        $recent     = array_slice($values, -$window);
        $movingAvg  = array_sum($recent) / count($recent);

        // Basit lineer trend (en küçük kareler)
        $n     = count($values);
        $x     = range(1, $n);
        $xMean = array_sum($x) / $n;
        $yMean = array_sum($values) / $n;

        $numerator   = 0;
        $denominator = 0;
        for ($i = 0; $i < $n; $i++) {
            $numerator   += ($x[$i] - $xMean) * ($values[$i] - $yMean);
            $denominator += ($x[$i] - $xMean) ** 2;
        }

        $slope     = $denominator != 0 ? $numerator / $denominator : 0;
        $intercept = $yMean - $slope * $xMean;
        $nextMonth = $intercept + $slope * ($n + 1);

        // İkisinin ağırlıklı ortalaması
        $forecast = ($movingAvg * 0.6) + ($nextMonth * 0.4);
        $forecast = max(0, round($forecast, 2));

        // Standart sapma ile confidence
        $variance   = array_sum(array_map(fn($v) => ($v - $yMean) ** 2, $values)) / $n;
        $stdDev     = sqrt($variance);
        $cv         = $yMean > 0 ? ($stdDev / $yMean) : 1;
        $confidence = $cv < 0.2 ? 'high' : ($cv < 0.4 ? 'medium' : 'low');

        return [
            'forecast'       => $forecast,
            'moving_avg'     => round($movingAvg, 2),
            'trend_slope'    => round($slope, 2),
            'confidence'     => $confidence,
            'method'         => 'weighted_avg_linear',
            'monthly_data'   => $monthlyData,
        ];
    }

    /**
     * SEVIYE 5: Anomali tespiti
     * Kavram: Z-score ile outlier tespiti
     */
    public function detectAnomalies(int $userId = 1, float $threshold = 2.0): Collection
    {
        $transactions = Transaction::where('user_id', $userId)
            ->expenses()
            ->get(['id', 'amount', 'transaction_date', 'description', 'category']);

        if ($transactions->isEmpty()) {
            return collect();
        }

        $amounts = $transactions->pluck('amount')->map(fn($a) => abs($a));
        $mean    = $amounts->avg();
        $stdDev  = sqrt($amounts->map(fn($a) => ($a - $mean) ** 2)->avg());

        return $transactions->filter(function ($tx) use ($mean, $stdDev, $threshold) {
            $zScore = $stdDev > 0 ? abs((abs($tx->amount) - $mean) / $stdDev) : 0;
            $tx->z_score = round($zScore, 2);
            return $zScore >= $threshold;
        })->values();
    }

    /**
     * Top harcama kategorileri özet
     */
    public function getTopCategories(int $userId = 1, int $limit = 5): Collection
    {
        return $this->getByCategory($userId)->take($limit);
    }

    private function parsePeriod(string $period): array
    {
        return match ($period) {
            'this_month'  => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'last_month'  => [now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
            'this_year'   => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'last_30'     => [now()->subDays(30)->toDateString(), now()->toDateString()],
            'last_90'     => [now()->subDays(90)->toDateString(), now()->toDateString()],
            default       => [now()->subDays(30)->toDateString(), now()->toDateString()],
        };
    }
}
