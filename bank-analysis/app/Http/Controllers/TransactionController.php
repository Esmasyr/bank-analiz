<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\DataCleaningService;
use App\Services\AnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class TransactionController extends Controller
{
    public function __construct(
        private DataCleaningService $cleaner,
        private AnalysisService $analysis
    ) {}

    // ─── SEVIYE 1: CRUD ──────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Transaction::query()->orderByDesc('transaction_date');

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->type === 'income') {
            $query->income();
        } elseif ($request->type === 'expense') {
            $query->expenses();
        }

        if ($request->search) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->period) {
            [$start, $end] = $this->parsePeriod($request->period);
            $query->inPeriod($start, $end);
        }

        $transactions = $query->paginate(20)->withQueryString();
        $categories   = Transaction::distinct()->pluck('category')->filter()->sort()->values();
        $summary      = $this->analysis->getSummary(1, $request->period);

        return view('transactions.index', compact('transactions', 'categories', 'summary'));
    }

    public function create()
    {
        $categories = $this->getDefaultCategories();
        return view('transactions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description'      => 'nullable|string|max:255',
            'amount'           => 'required|numeric',
            'transaction_date' => 'required|date',
            'category'         => 'nullable|string|max:100',
            'location'         => 'nullable|string|max:100',
            'type'             => 'required|in:income,expense',
        ]);

        // Gider ise negatif yap
        $amount = $validated['type'] === 'expense'
            ? -abs((float) $validated['amount'])
            : abs((float) $validated['amount']);

        $cleaned = $this->cleaner->cleanDescription($validated['description'] ?? '');

        Transaction::create([
            'user_id'          => 1,
            'amount'           => $amount,
            'description'      => $cleaned,
            'transaction_date' => $validated['transaction_date'],
            'category'         => $validated['category'] ?? $this->cleaner->inferCategory($cleaned, $amount),
            'location'         => $this->cleaner->cleanLocation($validated['location'] ?? ''),
            'transaction_type' => $validated['type'] === 'income' ? 'credit' : 'debit',
            'source'           => 'manual',
        ]);

        return redirect()->route('transactions.index')
            ->with('success', 'İşlem başarıyla eklendi!');
    }

    public function edit(Transaction $transaction)
    {
        $categories = $this->getDefaultCategories();
        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'description'      => 'nullable|string|max:255',
            'amount'           => 'required|numeric',
            'transaction_date' => 'required|date',
            'category'         => 'nullable|string|max:100',
            'type'             => 'required|in:income,expense',
        ]);

        $amount = $validated['type'] === 'expense'
            ? -abs((float) $validated['amount'])
            : abs((float) $validated['amount']);

        $transaction->update([
            'amount'           => $amount,
            'description'      => $this->cleaner->cleanDescription($validated['description'] ?? ''),
            'transaction_date' => $validated['transaction_date'],
            'category'         => $validated['category'],
            'transaction_type' => $validated['type'] === 'income' ? 'credit' : 'debit',
        ]);

        return redirect()->route('transactions.index')
            ->with('success', 'İşlem güncellendi!');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return redirect()->route('transactions.index')
            ->with('success', 'İşlem silindi!');
    }

    // ─── SEÇENEK 2: CSV YÜKLEME ──────────────────────────────────────

    public function importForm()
    {
        return view('transactions.import');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:51200', // 50MB
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        try {
            // league/csv ile oku
            $csv = Reader::createFromPath($path, 'r');
            $csv->setHeaderOffset(0);

            $records = $csv->getRecords();

            DB::beginTransaction();

            foreach ($records as $offset => $record) {
                try {
                    // Banka formatı mı? Standart format mı?
                    if (isset($record['TransactionID'])) {
                        $cleaned = $this->cleaner->parseBankCsvRow($record);
                    } else {
                        // Standart format: date, description, amount, category
                        $cleaned = $this->cleaner->cleanRow([
                            'description' => $record['description'] ?? $record['Description'] ?? '',
                            'amount'      => $record['amount'] ?? $record['Amount'] ?? 0,
                            'date'        => $record['date'] ?? $record['Date'] ?? '',
                            'location'    => $record['location'] ?? $record['Location'] ?? '',
                        ]);
                        $cleaned['user_id'] = 1;
                        $cleaned['source']  = 'csv';
                    }

                    // External ID varsa duplicate kontrolü
                    if (!empty($cleaned['external_id'])) {
                        $exists = Transaction::where('external_id', $cleaned['external_id'])->exists();
                        if ($exists) {
                            $skipped++;
                            continue;
                        }
                    }

                    Transaction::create($cleaned);
                    $inserted++;

                    // Her 500 kayıtta commit et (bellek yönetimi)
                    if ($inserted % 500 === 0) {
                        DB::commit();
                        DB::beginTransaction();
                    }
                } catch (\Exception $e) {
                    $errors[] = "Satır {$offset}: " . $e->getMessage();
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['csv_file' => 'CSV okunamadı: ' . $e->getMessage()]);
        }

        $message = "{$inserted} işlem eklendi.";
        if ($skipped > 0) $message .= " {$skipped} tekrar atlandı.";
        if (!empty($errors)) $message .= ' ' . count($errors) . ' hata oluştu.';

        return redirect()->route('transactions.index')->with('success', $message);
    }

    // ─── SEÇENEK 3: API SİMÜLASYONU ─────────────────────────────────

    public function apiSimulate(Request $request)
    {
        // Gerçekçi banka işlemi simülasyonu
        $categories = ['food', 'transport', 'shopping', 'medical', 'utilities', 'entertainment', 'atm'];
        $locations  = ['Mumbai', 'Delhi', 'Bangalore', 'Chennai', 'Hyderabad', 'Pune', 'Jaipur'];
        $descs      = [
            'food'          => ['Restaurant Purchase', 'Swiggy Order', 'Zomato Delivery', 'Grocery Store'],
            'transport'     => ['Uber Ride', 'Ola Cab', 'Metro Card Recharge', 'Petrol Station'],
            'shopping'      => ['Amazon Purchase', 'Flipkart Order', 'Mall Shopping', 'Online Store'],
            'medical'       => ['Hospital Bill', 'Pharmacy Purchase', 'Doctor Consultation'],
            'utilities'     => ['Electricity Bill', 'Mobile Recharge', 'Internet Bill', 'DTH Recharge'],
            'entertainment' => ['Netflix Subscription', 'Movie Tickets', 'Spotify Premium'],
            'atm'           => ['ATM Withdrawal', 'Cash Withdrawal'],
        ];

        $generated = 0;
        $count = min((int) $request->count ?? 10, 50); // Max 50

        for ($i = 0; $i < $count; $i++) {
            $cat    = $categories[array_rand($categories)];
            $desc   = $descs[$cat][array_rand($descs[$cat])];
            $amount = -abs(fake()->randomFloat(2, 50, 5000));

            Transaction::create([
                'user_id'          => 1,
                'amount'           => $amount,
                'description'      => $desc,
                'category'         => $cat,
                'transaction_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                'location'         => $locations[array_rand($locations)],
                'transaction_type' => 'debit',
                'source'           => 'api',
            ]);
            $generated++;
        }

        return redirect()->route('transactions.index')
            ->with('success', "{$generated} simüle işlem oluşturuldu!");
    }

    // ─── YARDIMCI ────────────────────────────────────────────────────

    private function parsePeriod(string $period): array
    {
        return match ($period) {
            'this_month' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'last_month' => [now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
            'last_30'    => [now()->subDays(30)->toDateString(), now()->toDateString()],
            'last_90'    => [now()->subDays(90)->toDateString(), now()->toDateString()],
            'this_year'  => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            default      => [now()->subDays(30)->toDateString(), now()->toDateString()],
        };
    }

    private function getDefaultCategories(): array
    {
        return [
            'food' => '🍔 Yemek',
            'transport' => '🚗 Ulaşım',
            'shopping' => '🛍️ Alışveriş',
            'medical' => '💊 Sağlık',
            'utilities' => '💡 Faturalar',
            'education' => '📚 Eğitim',
            'entertainment' => '🎬 Eğlence',
            'salary' => '💰 Maaş',
            'transfer' => '💸 Transfer',
            'atm' => '🏧 ATM',
            'emi' => '🏦 EMI/Kredi',
            'income' => '📈 Gelir',
            'other' => '📦 Diğer',
        ];
    }
}
