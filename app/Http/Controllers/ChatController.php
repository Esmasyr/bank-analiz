<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $message = mb_strtolower(trim($request->message), 'UTF-8');
        $userId  = Auth::id();

        try {
            $stats = $this->getStats($userId);
            $reply = $this->resolve($message, $stats);
        } catch (\Exception $e) {
            $reply = 'Veri okuma hatası: ' . $e->getMessage();
        }

        return response()->json(['reply' => $reply]);
    }

    // ── VERİ ÇEKME ────────────────────────────────────────────────

    private function getStats(int $userId): array
    {
        $table   = 'transactions';
        $columns = DB::getSchemaBuilder()->getColumnListing($table);

        // user kolonu — user_id veya uploaded_by_user_id
        $userCol = in_array('user_id', $columns) ? 'user_id' : 'uploaded_by_user_id';

        $q = fn() => DB::table($table)->where($userCol, $userId);

        $totalCount    = $q()->count();
        $totalIncome   = (float) $q()->where('type', 'deposit')->where('status', 'completed')->sum('amount');
        $totalExpense  = (float) $q()->where('type', 'withdrawal')->where('status', 'completed')->sum('amount');
        $totalTransfer = (float) $q()->where('type', 'transfer')->where('status', 'completed')->sum('amount');
        $balance       = $totalIncome - $totalExpense - $totalTransfer;
        $avgAmount     = (float) ($q()->avg('amount') ?? 0);
        $maxAmount     = (float) ($q()->max('amount') ?? 0);
        $minAmount     = (float) ($q()->where('amount', '>', 0)->min('amount') ?? 0);
        $pending       = $q()->where('status', 'pending')->count();
        $completed     = $q()->where('status', 'completed')->count();
        $failed        = $q()->where('status', 'failed')->count();

        // Bu ay
        $thisMonthIncome  = (float) $q()->where('type', 'deposit')
            ->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)
            ->sum('amount');
        $thisMonthExpense = (float) $q()->where('type', 'withdrawal')
            ->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)
            ->sum('amount');
        $thisMonthCount   = $q()->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)->count();

        // Geçen ay
        $lastMonthIncome  = (float) $q()->where('type', 'deposit')
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('amount');
        $lastMonthExpense = (float) $q()->where('type', 'withdrawal')
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('amount');

        // Son 6 ay aylık gider
        $monthlyExpenses = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $monthlyExpenses[$m->format('M Y')] = (float) $q()->where('type', 'withdrawal')
                ->whereYear('created_at', $m->year)
                ->whereMonth('created_at', $m->month)
                ->sum('amount');
        }

        // En yüksek harcama kategorisi
        $topCategoryRow = null;
        if (in_array('category', $columns)) {
            $topCategoryRow = DB::table($table)
                ->where($userCol, $userId)
                ->where('type', 'withdrawal')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->orderByDesc('total')
                ->first();
        }

        // Son işlem
        $lastTxn = $q()->orderByDesc('created_at')->first();

        // Bu hafta harcama
        $thisWeekExpense = (float) $q()->where('type', 'withdrawal')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');

        // Günlük ort. (son 30 gün)
        $last30Expense = (float) $q()->where('type', 'withdrawal')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('amount');
        $dailyAvg = round($last30Expense / 30, 2);

        // En yüksek 3 işlem
        $topTransactions = $q()->orderByDesc('amount')->limit(3)->get();

        return compact(
            'totalCount', 'totalIncome', 'totalExpense', 'totalTransfer',
            'balance', 'avgAmount', 'maxAmount', 'minAmount',
            'pending', 'completed', 'failed',
            'thisMonthIncome', 'thisMonthExpense', 'thisMonthCount',
            'lastMonthIncome', 'lastMonthExpense',
            'monthlyExpenses', 'topCategoryRow', 'lastTxn',
            'thisWeekExpense', 'dailyAvg', 'topTransactions'
        );
    }

    // ── ANA ÇÖZÜMLEME ─────────────────────────────────────────────

    private function resolve(string $msg, array $s): string
    {
        if ($this->has($msg, ['merhaba','selam','hey','hello','hi','günaydın','iyi günler','iyi akşamlar','naber','nasılsın']))
            return $this->greet($s);

        if ($this->has($msg, ['bakiye','ne kadar param','hesabım','hesabımda','param ne kadar','kalan','net durum','toplam param','param var mı']))
            return $this->balance($s);

        if ($this->has($msg, ['gelir','yatırım','deposit','giriş','kazanç','kazandım','para girdi','ne kadar girdi','toplam gelir','para geldi']))
            return $this->income($s);

        if ($this->has($msg, ['gider','harcama','harcadım','çekim','withdrawal','para çıktı','çıkış','masraf','harcamalar','ne harcadım','ne kadar harcadım']))
            return $this->expense($s);

        if ($this->has($msg, ['bu ay','bu ayın','aylık','bu ayki','mevcut ay','bu ay özeti','ayın durumu']))
            return $this->thisMonth($s);

        if ($this->has($msg, ['geçen ay','geçen ayın','önceki ay','geçen ayki','bir önceki ay']))
            return $this->lastMonth($s);

        if ($this->has($msg, ['bu hafta','haftalık','haftanın','bu haftaki','son 7 gün']))
            return $this->thisWeek($s);

        if ($this->has($msg, ['günlük','her gün','günde ne kadar','ortalama günlük','bugün']))
            return $this->daily($s);

        if ($this->has($msg, ['en yüksek','maksimum','max','en büyük','en fazla','max işlem']))
            return $this->maxTxn($s);

        if ($this->has($msg, ['en düşük','minimum','min','en küçük','en az']))
            return $this->minTxn($s);

        if ($this->has($msg, ['ortalama','ort','average','tipik']))
            return $this->average($s);

        if ($this->has($msg, ['kategori','neye harcadım','nereye harcadım','hangi kategori','ne için','en çok neye','harcama kalemi']))
            return $this->category($s);

        if ($this->has($msg, ['bekleyen','pending','onay bekliyor','tamamlanmamış','bekliyor']))
            return $this->pending($s);

        if ($this->has($msg, ['tamamlanan','completed','bitti','tamamlandı','biten','başarılı']))
            return $this->completedTxn($s);

        if ($this->has($msg, ['başarısız','failed','hatalı','iptal','reddedilen']))
            return $this->failedTxn($s);

        if ($this->has($msg, ['kaç işlem','işlem sayısı','toplam işlem','kaç tane','kaç kayıt']))
            return $this->count($s);

        if ($this->has($msg, ['son işlem','en son','son hareket','son para','son kayıt','son transfer']))
            return $this->lastTransaction($s);

        if ($this->has($msg, ['trend','tahmin','gelecek','artış','azalış','yükseliyor','düşüyor','eğilim','gidiş','harcama trendi','nasıl gidiyor']))
            return $this->trend($s);

        if ($this->has($msg, ['tasarruf','birikim','biriktir','ne kadar biriktirdim','kâr']))
            return $this->savings($s);

        if ($this->has($msg, ['transfer','havale','eft']))
            return $this->transfer($s);

        if ($this->has($msg, ['özet','rapor','genel durum','durum','analiz','nasıl','finansal durum','genel bakış']))
            return $this->summary($s);

        if ($this->has($msg, ['tavsiye','öneri','ne yapmalıyım','ne önerirsin','nasıl tasarruf','ipucu']))
            return $this->advice($s);

        if ($this->has($msg, ['yardım','help','ne yapabilirsin','neler yapabilirsin','ne sorabilir','komutlar']))
            return $this->help();

        if ($this->has($msg, ['teşekkür','teşekkürler','sağ ol','eyw','thanks','thank you','tşk','süper','harika','mükemmel']))
            return 'Rica ederim! Başka bir konuda yardımcı olabilir miyim?';

        return $this->unknown();
    }

    // ── CEVAP METODLARI ──────────────────────────────────────────

    private function greet(array $s): string
    {
        $h    = (int) now()->format('H');
        $time = $h < 12 ? 'Günaydın' : ($h < 18 ? 'İyi günler' : 'İyi akşamlar');
        $name = Auth::user()->name;

        if ($s['totalCount'] === 0) {
            return "{$time}, {$name}! Henüz işlem verisi yok.\nCSV yükleyerek veya manuel işlem ekleyerek başlayabilirsiniz.";
        }

        $dir = $s['balance'] >= 0 ? 'pozitif' : 'negatif';
        return "{$time}, {$name}!\n"
            . "• Bakiye: {$this->money($s['balance'])} ({$dir})\n"
            . "• Toplam işlem: {$s['totalCount']}\n"
            . "• Bu ay harcama: {$this->money($s['thisMonthExpense'])}\n\n"
            . "Ne öğrenmek istersiniz?";
    }

    private function balance(array $s): string
    {
        if ($s['totalCount'] === 0) return 'Henüz işlem verisi bulunmuyor.';
        $durum = $s['balance'] >= 0 ? 'pozitif' : 'negatif';
        return "Hesap Durumu:\n"
            . "• Net bakiye: {$this->money($s['balance'])} ({$durum})\n"
            . "• Toplam gelir: {$this->money($s['totalIncome'])}\n"
            . "• Toplam gider: {$this->money($s['totalExpense'])}\n"
            . "• Transfer: {$this->money($s['totalTransfer'])}";
    }

    private function income(array $s): string
    {
        if ($s['totalIncome'] === 0.0) return 'Tamamlanmış gelir işlemi bulunamadı.';
        $change = $this->monthChange($s['lastMonthIncome'], $s['thisMonthIncome']);
        return "Gelir Özeti:\n"
            . "• Toplam gelir: {$this->money($s['totalIncome'])}\n"
            . "• Bu ay: {$this->money($s['thisMonthIncome'])}\n"
            . "• Geçen ay: {$this->money($s['lastMonthIncome'])}\n"
            . "• Değişim: {$change}";
    }

    private function expense(array $s): string
    {
        if ($s['totalExpense'] === 0.0) return 'Tamamlanmış gider işlemi bulunamadı.';
        $change = $this->monthChange($s['lastMonthExpense'], $s['thisMonthExpense']);
        $cat    = $s['topCategoryRow']
            ? "\n• En büyük kategori: {$s['topCategoryRow']->category} ({$this->money((float)$s['topCategoryRow']->total)})"
            : '';
        return "Gider Özeti:\n"
            . "• Toplam gider: {$this->money($s['totalExpense'])}\n"
            . "• Bu ay: {$this->money($s['thisMonthExpense'])}\n"
            . "• Geçen ay: {$this->money($s['lastMonthExpense'])}\n"
            . "• Değişim: {$change}" . $cat;
    }

    private function thisMonth(array $s): string
    {
        $net = $s['thisMonthIncome'] - $s['thisMonthExpense'];
        $dir = $net >= 0 ? 'net kâr' : 'net açık';
        return "Bu Ay — " . now()->format('F Y') . ":\n"
            . "• İşlem sayısı: {$s['thisMonthCount']}\n"
            . "• Gelir: {$this->money($s['thisMonthIncome'])}\n"
            . "• Gider: {$this->money($s['thisMonthExpense'])}\n"
            . "• " . ucfirst($dir) . ": {$this->money(abs($net))}";
    }

    private function lastMonth(array $s): string
    {
        $net = $s['lastMonthIncome'] - $s['lastMonthExpense'];
        $dir = $net >= 0 ? 'net kâr' : 'net açık';
        return "Geçen Ay — " . now()->subMonth()->format('F Y') . ":\n"
            . "• Gelir: {$this->money($s['lastMonthIncome'])}\n"
            . "• Gider: {$this->money($s['lastMonthExpense'])}\n"
            . "• " . ucfirst($dir) . ": {$this->money(abs($net))}";
    }

    private function thisWeek(array $s): string
    {
        $daily = $s['thisWeekExpense'] > 0 ? round($s['thisWeekExpense'] / max(now()->dayOfWeek, 1), 2) : 0;
        return "Bu Hafta:\n"
            . "• Toplam harcama: {$this->money($s['thisWeekExpense'])}\n"
            . "• Günlük ort.: {$this->money($daily)}";
    }

    private function daily(array $s): string
    {
        return "Günlük Ortalama (son 30 gün):\n"
            . "• Günlük harcama: {$this->money($s['dailyAvg'])}\n"
            . "• Aylık tahmini: {$this->money($s['dailyAvg'] * 30)}";
    }

    private function maxTxn(array $s): string
    {
        $top = $s['topTransactions'];
        if (!$top || count($top) === 0) return 'İşlem bulunamadı.';
        $lines = "En Yüksek 3 İşlem:\n";
        foreach ($top as $i => $t) {
            $desc   = !empty($t->description) ? " — {$t->description}" : '';
            $lines .= ($i + 1) . ". {$this->money((float)$t->amount)}{$desc}\n";
        }
        return trim($lines);
    }

    private function minTxn(array $s): string
    {
        return "En düşük işlem tutarı: {$this->money($s['minAmount'])}";
    }

    private function average(array $s): string
    {
        return "Ortalamalar:\n"
            . "• Tüm işlemler ort.: {$this->money($s['avgAmount'])}\n"
            . "• Günlük harcama ort. (30 gün): {$this->money($s['dailyAvg'])}";
    }

    private function category(array $s): string
    {
        if (!$s['topCategoryRow']) {
            return 'Kategori verisi bulunamadı. İşlemlerinize kategori ekleyebilirsiniz.';
        }
        return "En Yüksek Harcama Kategorisi:\n"
            . "• Kategori: {$s['topCategoryRow']->category}\n"
            . "• Toplam: {$this->money((float)$s['topCategoryRow']->total)}";
    }

    private function pending(array $s): string
    {
        if ($s['pending'] === 0) return 'Bekleyen işleminiz bulunmuyor.';
        return "{$s['pending']} adet bekleyen işleminiz var.\nİşlemler sayfasından takip edebilirsiniz.";
    }

    private function completedTxn(array $s): string
    {
        $oran = $s['totalCount'] > 0 ? round(($s['completed'] / $s['totalCount']) * 100) : 0;
        return "Tamamlanan: {$s['completed']} işlem\nBaşarı oranı: %{$oran}";
    }

    private function failedTxn(array $s): string
    {
        if ($s['failed'] === 0) return 'Başarısız işleminiz bulunmuyor.';
        return "{$s['failed']} adet başarısız işleminiz var.\nİncelemenizi öneririm.";
    }

    private function count(array $s): string
    {
        return "İşlem Sayıları:\n"
            . "• Toplam: {$s['totalCount']}\n"
            . "• Tamamlanan: {$s['completed']}\n"
            . "• Bekleyen: {$s['pending']}\n"
            . "• Başarısız: {$s['failed']}";
    }

    private function lastTransaction(array $s): string
    {
        if (!$s['lastTxn']) return 'Henüz hiç işlem bulunmuyor.';
        $t    = $s['lastTxn'];
        $type = ['deposit' => 'Yatırım', 'withdrawal' => 'Çekim', 'transfer' => 'Transfer'][$t->type] ?? $t->type;
        $date = \Carbon\Carbon::parse($t->created_at)->format('d.m.Y H:i');
        $desc = !empty($t->description) ? "\n• Açıklama: {$t->description}" : '';
        return "Son İşlem:\n"
            . "• Tür: {$type}\n"
            . "• Tutar: {$this->money((float)$t->amount)}\n"
            . "• Durum: " . ($t->status ?? '-') . "\n"
            . "• Tarih: {$date}" . $desc;
    }

    private function trend(array $s): string
    {
        $expenses = array_values($s['monthlyExpenses']);
        $nonZero  = array_filter($expenses, fn($v) => $v > 0);

        if (count($nonZero) < 2) {
            return 'Trend analizi için yeterli veri yok. Daha fazla işlem ekleyin.';
        }

        $last = end($expenses);
        $prev = $expenses[count($expenses) - 2];

        if ($prev <= 0) return 'Geçen ay verisi yok, karşılaştırma yapılamıyor.';

        $diff = $last - $prev;
        $pct  = round(abs($diff / $prev) * 100);
        $dir  = $diff >= 0 ? 'arttı' : 'azaldı';

        // Lineer regresyon
        $n   = count($expenses);
        $xs  = range(0, $n - 1);
        $sx  = array_sum($xs);
        $sy  = array_sum($expenses);
        $sxy = array_sum(array_map(fn($x, $y) => $x * $y, $xs, $expenses));
        $sxx = array_sum(array_map(fn($x) => $x * $x, $xs));
        $den = ($n * $sxx - $sx * $sx);
        $slope     = $den != 0 ? ($n * $sxy - $sx * $sy) / $den : 0;
        $intercept = $n > 0 ? ($sy - $slope * array_sum($xs) / $n) / $n : 0;
        $nextMonth = max(0, round($intercept + $slope * $n));

        return "Harcama Trendi:\n"
            . "• Son ay: {$this->money($last)}\n"
            . "• Önceki ay: {$this->money($prev)}\n"
            . "• Değişim: %{$pct} {$dir}\n"
            . "• Sonraki ay tahmini: {$this->money($nextMonth)}\n"
            . ($diff > 0 ? "Dikkat: Harcamalarınız artıyor." : "Olumlu: Harcamalarınız azalıyor.");
    }

    private function savings(array $s): string
    {
        $net = $s['totalIncome'] - $s['totalExpense'];
        if ($s['totalIncome'] <= 0) return 'Gelir verisi bulunamadı.';
        $rate = round(($net / $s['totalIncome']) * 100);
        return "Tasarruf Durumu:\n"
            . "• Net birikim: {$this->money($net)}\n"
            . "• Tasarruf oranı: %{$rate}\n"
            . "• Bu ay net: {$this->money($s['thisMonthIncome'] - $s['thisMonthExpense'])}";
    }

    private function transfer(array $s): string
    {
        return "Transfer Özeti:\n"
            . "• Toplam transfer: {$this->money($s['totalTransfer'])}\n"
            . "İşlemler sayfasından detay görebilirsiniz.";
    }

    private function summary(array $s): string
    {
        $health = $s['balance'] >= 0 ? 'sağlıklı' : 'dikkat gerektiriyor';
        $net    = $s['thisMonthIncome'] - $s['thisMonthExpense'];
        $dir    = $net >= 0 ? 'fazla' : 'açık';
        $pend   = $s['pending'] > 0 ? "\n• Bekleyen: {$s['pending']} işlem" : '';
        return "Finansal Özet:\n"
            . "• Durum: {$health}\n"
            . "• Bakiye: {$this->money($s['balance'])}\n"
            . "• Bu ay gelir: {$this->money($s['thisMonthIncome'])}\n"
            . "• Bu ay gider: {$this->money($s['thisMonthExpense'])}\n"
            . "• Bu ay net: {$this->money(abs($net))} {$dir}\n"
            . "• Toplam işlem: {$s['totalCount']}" . $pend;
    }

    private function advice(array $s): string
    {
        $tips = [];
        if ($s['pending'] > 0) $tips[] = "{$s['pending']} bekleyen işleminizi kontrol edin.";
        if ($s['failed'] > 0)  $tips[] = "{$s['failed']} başarısız işleminiz var — incelemeniz önerilir.";
        $net = $s['thisMonthIncome'] - $s['thisMonthExpense'];
        if ($net < 0) $tips[] = "Bu ay giderleriniz gelirinizi aşıyor. Harcamalarınızı gözden geçirin.";
        $expenses = array_values($s['monthlyExpenses']);
        if (count($expenses) >= 2) {
            $last = end($expenses); $prev = $expenses[count($expenses) - 2];
            if ($prev > 0 && $last > $prev * 1.2) {
                $tips[] = "Harcamalarınız geçen aya göre %" . round((($last-$prev)/$prev)*100) . " arttı.";
            }
        }
        if ($s['dailyAvg'] > 0) $tips[] = "Günlük ortalama harcamanız: {$this->money($s['dailyAvg'])}.";

        if (empty($tips)) return "Finansal durumunuz iyi görünüyor!\nDüzenli takip yapıyorsunuz.";
        return "Öneriler:\n" . implode("\n", array_map(fn($t, $i) => ($i+1).". $t", $tips, array_keys($tips)));
    }

    private function help(): string
    {
        return "Anlayabildiğim konular:\n\n"
            . "• bakiye — net hesap durumu\n"
            . "• gelir / gider — özet\n"
            . "• bu ay / geçen ay — dönem analizi\n"
            . "• bu hafta / günlük — kısa dönem\n"
            . "• en yüksek / en düşük — işlem limitleri\n"
            . "• ortalama — tipik işlem tutarı\n"
            . "• kategori — harcama kalemi\n"
            . "• bekleyen / başarısız — durum\n"
            . "• son işlem — son hareket\n"
            . "• trend — tahmin ve eğilim\n"
            . "• tasarruf — birikim analizi\n"
            . "• özet — genel finansal rapor\n"
            . "• tavsiye — akıllı öneriler";
    }

    private function unknown(): string
    {
        return "Bu soruyu anlayamadım.\nÖrnek: \"bakiye\", \"bu ay\", \"trend\" yazabilirsiniz.\nTüm seçenekler için \"yardım\" yazın.";
    }

    // ── YARDIMCI ──────────────────────────────────────────────────

    private function has(string $msg, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (str_contains($msg, mb_strtolower($kw, 'UTF-8'))) return true;
        }
        return false;
    }

    private function money(float $n): string
    {
        return '₺' . number_format(abs($n), 2, ',', '.');
    }

    private function monthChange(float $prev, float $curr): string
    {
        if ($prev <= 0 && $curr <= 0) return 'veri yok';
        if ($prev <= 0) return 'ilk kayıt';
        $pct = round((($curr - $prev) / $prev) * 100);
        return $pct >= 0 ? "%{$pct} artış" : "%" . abs($pct) . " azalış";
    }
}