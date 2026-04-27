# 🏦 BankAnaliz — Laravel Finansal Analiz Sistemi

Kullanıcı işlemlerini analiz eden, harcama alışkanlıklarını çıkaran ve gelecek için tahmin üreten profesyonel banka analiz sistemi.

## 🎯 Özellikler

| Seviye | Özellik |
|--------|---------|
| ✅ Seviye 1 | CRUD + Manuel işlem ekleme |
| ✅ Seviye 2 | Otomatik kategori sistemi |
| ✅ Seviye 3 | Analiz (toplamlar, aylık trend, kategori grupları) |
| ✅ Seviye 4 | Grafikler (Chart.js ile bar, pie, line, doughnut) |
| ✅ Seviye 5 | Tahmin (Moving Average + Lineer Regresyon) + Anomali tespiti |

---

## ⚡ Hızlı Kurulum

### Gereksinimler
- PHP 8.2+
- Composer
- MySQL 8.0+ veya SQLite
- Node.js (isteğe bağlı, CDN kullanılıyor)

### Adım 1: Laravel Projesi Oluştur

```bash
# Yeni Laravel projesi oluştur
composer create-project laravel/laravel bank-analiz
cd bank-analiz
```

### Adım 2: Dosyaları Kopyala

Tüm proje dosyalarını (`app/`, `database/`, `resources/`, `routes/`) yeni projenin içine kopyala:

```bash
# Eski dizinden:
cp -r bank-analysis/app/* bank-analiz/app/
cp -r bank-analysis/database/* bank-analiz/database/
cp -r bank-analysis/resources/views/* bank-analiz/resources/views/
cp bank-analysis/routes/web.php bank-analiz/routes/web.php
```

### Adım 3: Bağımlılık Ekle

```bash
composer require league/csv:^9.15
```

### Adım 4: Veritabanı Ayarla

**.env** dosyasını düzenle:

```env
# MySQL için:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bank_analiz
DB_USERNAME=root
DB_PASSWORD=sifreniz

# VEYA SQLite için (daha kolay):
DB_CONNECTION=sqlite
```

SQLite için:
```bash
touch database/database.sqlite
```

### Adım 5: Migration ve Seed

```bash
php artisan key:generate
php artisan migrate
php artisan db:seed   # Örnek veriler için (isteğe bağlı)
```

### Adım 6: Çalıştır

```bash
php artisan serve
# http://localhost:8000 adresini aç
```

---

## 📊 Veri Girişi (3 Yol)

### 1. Manuel Giriş
`/transactions/create` → Form doldur → Kaydet

### 2. CSV Yükleme
`/transactions/import/form` → CSV seç → Yükle

**Desteklenen Formatlar:**

**Banka Ekstresi (bank_transactions.csv):**
```
TransactionID,CustomerID,CustomerDOB,CustGender,CustLocation,CustAccountBalance,TransactionDate,TransactionTime,TransactionAmount (INR)
T1,C5841053,10/1/94,F,JAMSHEDPUR,17819.05,2/8/16,143207,25
```

**Standart Format:**
```
date,description,amount,category,location
2024-01-15,Restoran Ödemesi,-450,food,Mumbai
2024-01-20,Maaş,45000,salary,
```

### 3. API Simülasyonu
Sidebar'daki "Üret" butonu → İstenen kadar sahte işlem oluşturur

---

## 🧹 Veri Temizleme (DataCleaningService)

```php
// Otomatik yapılan temizlikler:
"PIZZA RESTAURANT!!!"  →  "Pizza Restaurant"       // Normalize + özel karakter temizle
"2/8/16"               →  "2016-08-02"             // Tarih format tespiti
"  MUMBAI  "           →  "Mumbai"                 // Trim + Title Case
"food"                 →  Kategori: "food"          // Keyword matching
```

---

## 📈 Analiz Metodolojisi

### Tahmin Algoritması
```
Tahmin = (Moving Average × 0.6) + (Lineer Regresyon × 0.4)

Moving Average: Son 3 ayın ortalaması
Lineer Regresyon: y = mx + b (en küçük kareler yöntemi)
```

### Anomali Tespiti
```
Z-Score = (değer - ortalama) / standart_sapma
Z-Score ≥ 2.0 → Anomali işaret edilir
```

---

## 🗄️ Veritabanı Yapısı

```sql
transactions
├── id
├── user_id          (varsayılan: 1)
├── amount           (negatif=gider, pozitif=gelir)
├── category         (otomatik veya manuel)
├── transaction_date
├── description
├── location
├── transaction_type (debit/credit)
├── balance_after
├── source           (manual/csv/api)
└── external_id      (CSV'den gelen TransactionID)
```

---

## 🔧 PHP.ini Ayarları (Büyük CSV için)

`php.ini` veya `.htaccess`'e ekle:
```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 512M
```

---

## 📁 Proje Yapısı

```
app/
├── Http/Controllers/
│   ├── DashboardController.php    # Ana analiz sayfası
│   └── TransactionController.php  # CRUD + CSV + API
├── Models/
│   └── Transaction.php
└── Services/
    ├── AnalysisService.php        # Tüm istatistik ve tahmin
    └── DataCleaningService.php    # Veri temizleme

resources/views/
├── layouts/app.blade.php          # Ana şablon (sidebar + nav)
├── dashboard/index.blade.php      # Dashboard + grafikler
└── transactions/
    ├── index.blade.php            # Liste + filtreler
    ├── create.blade.php           # Manuel giriş formu
    ├── edit.blade.php
    └── import.blade.php           # CSV yükleme
```
