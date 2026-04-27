<?php

namespace App\Services;

/**
 * Veri Temizleme Servisi
 * Öğrenilen kavramlar: data cleaning, normalizasyon, validasyon
 */
class DataCleaningService
{
    // Kategori anahtar kelimeleri - description'dan otomatik kategori çıkar
    private array $categoryKeywords = [
        'food'       => ['food', 'restaurant', 'cafe', 'eat', 'meal', 'hotel', 'dining', 'pizza', 'burger', 'swiggy', 'zomato'],
        'transport'  => ['transport', 'uber', 'ola', 'taxi', 'petrol', 'fuel', 'metro', 'bus', 'train', 'auto'],
        'shopping'   => ['shop', 'amazon', 'flipkart', 'myntra', 'mall', 'store', 'market', 'purchase'],
        'medical'    => ['hospital', 'clinic', 'pharmacy', 'medicine', 'doctor', 'health', 'medical'],
        'utilities'  => ['electricity', 'water', 'gas', 'bill', 'internet', 'mobile', 'recharge', 'dth'],
        'education'  => ['school', 'college', 'university', 'fee', 'tuition', 'course', 'book'],
        'entertainment' => ['movie', 'netflix', 'hotstar', 'spotify', 'game', 'entertainment', 'cinema'],
        'salary'     => ['salary', 'wages', 'payroll', 'income', 'credit'],
        'transfer'   => ['transfer', 'neft', 'imps', 'rtgs', 'upi'],
        'atm'        => ['atm', 'cash', 'withdrawal'],
        'emi'        => ['emi', 'loan', 'insurance', 'premium'],
    ];

    /**
     * Tek bir transaction row'u temizle
     */
    public function cleanRow(array $row): array
    {
        return [
            'description'      => $this->cleanDescription($row['description'] ?? ''),
            'amount'           => $this->cleanAmount($row['amount'] ?? 0),
            'transaction_date' => $this->cleanDate($row['date'] ?? ''),
            'location'         => $this->cleanLocation($row['location'] ?? ''),
            'category'         => $this->inferCategory($row['description'] ?? '', $row['amount'] ?? 0),
            'transaction_type' => $this->inferType($row['amount'] ?? 0),
        ];
    }

    /**
     * Açıklamayı temizle:
     * - Büyük/küçük harf normalize
     * - Gereksiz karakterler sil
     * - Boş değerleri standart hale getir
     */
    public function cleanDescription(string $desc): string
    {
        if (empty(trim($desc))) {
            return 'Açıklama yok';
        }

        // Küçük harfe çevir → title case yap
        $desc = mb_strtolower(trim($desc), 'UTF-8');
        $desc = mb_convert_case($desc, MB_CASE_TITLE, 'UTF-8');

        // Gereksiz karakterleri temizle (sadece harf, rakam, boşluk, nokta, tire bırak)
        $desc = preg_replace('/[^\w\s\.\-\,\/]/u', '', $desc);

        // Çoklu boşlukları tek boşluğa indir
        $desc = preg_replace('/\s+/', ' ', $desc);

        return trim($desc);
    }

    /**
     * Tutarı temizle ve normalize et
     */
    public function cleanAmount(mixed $amount): float
    {
        // String ise temizle
        if (is_string($amount)) {
            $amount = str_replace([',', '₹', '$', '₺', ' '], '', $amount);
        }

        $amount = (float) $amount;

        // Makul olmayan değerleri sıfırla
        if ($amount > 100_000_000 || is_nan($amount) || is_infinite($amount)) {
            return 0.0;
        }

        return round($amount, 2);
    }

    /**
     * Tarih formatlarını normalize et
     */
    public function cleanDate(string $date): ?string
    {
        if (empty(trim($date))) {
            return now()->toDateString();
        }

        // Farklı formatları dene
        $formats = [
            'd/m/y', 'd/m/Y', 'm/d/Y', 'Y-m-d',
            'd-m-Y', 'Y/m/d', 'd.m.Y',
        ];

        foreach ($formats as $format) {
            try {
                $dt = \DateTime::createFromFormat($format, trim($date));
                if ($dt && $dt->format($format) === trim($date)) {
                    return $dt->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Son çare: strtotime
        $ts = strtotime($date);
        return $ts ? date('Y-m-d', $ts) : now()->toDateString();
    }

    /**
     * Lokasyonu normalize et
     */
    public function cleanLocation(string $location): string
    {
        if (empty(trim($location))) {
            return '';
        }

        return mb_convert_case(mb_strtolower(trim($location), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Description'dan kategori çıkar (basit keyword matching)
     */
    public function inferCategory(string $description, float $amount): string
    {
        $desc = mb_strtolower($description, 'UTF-8');

        foreach ($this->categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($desc, $keyword)) {
                    return $category;
                }
            }
        }

        // Tutar pozitifse gelir, negatifse genel gider
        if ($amount > 0) {
            return 'income';
        }

        return 'other';
    }

    /**
     * İşlem tipini çıkar
     */
    public function inferType(float $amount): string
    {
        return $amount >= 0 ? 'credit' : 'debit';
    }

    /**
     * CSV satırını banka formatından parse et
     * Format: TransactionID,CustomerID,CustomerDOB,CustGender,CustLocation,
     *         CustAccountBalance,TransactionDate,TransactionTime,TransactionAmount
     */
    public function parseBankCsvRow(array $row): array
    {
        $amount = $this->cleanAmount($row['TransactionAmount (INR)'] ?? $row['amount'] ?? 0);

        // Banka CSV'sinde tüm işlemler gider olarak gelir (negatif yap)
        // Gerçekte bu veri setinde hepsini gider olarak ele alıyoruz
        $signedAmount = -abs($amount);

        return [
            'external_id'      => $row['TransactionID'] ?? null,
            'description'      => 'Banka İşlemi - ' . ($row['CustLocation'] ?? ''),
            'amount'           => $signedAmount,
            'transaction_date' => $this->cleanDate($row['TransactionDate'] ?? ''),
            'location'         => $this->cleanLocation($row['CustLocation'] ?? ''),
            'balance_after'    => $this->cleanAmount($row['CustAccountBalance'] ?? 0),
            'category'         => 'other',
            'transaction_type' => 'debit',
            'source'           => 'csv',
            'user_id'          => 1,
        ];
    }
}
