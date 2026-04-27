@extends('layouts.app')
@section('title', 'CSV Yükle')

@section('content')
<div class="max-w-2xl space-y-8">
    <div>
        <h2 class="text-3xl font-bold gradient-text">CSV Yükle</h2>
        <p class="text-slate-400 mt-1">Seçenek 2: Banka ekstresi veya standart CSV</p>
    </div>

    {{-- Format Bilgisi --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="glass rounded-2xl p-5 border border-indigo-500/20">
            <h3 class="font-semibold text-indigo-300 mb-3 flex items-center gap-2">
                <i class="fas fa-university"></i> Banka Ekstresi Formatı
            </h3>
            <p class="text-xs text-slate-400 mb-2">Yüklediğiniz CSV (bank_transactions.csv):</p>
            <code class="text-xs text-slate-300 bg-black/30 p-2 rounded-lg block leading-relaxed">
                TransactionID, CustomerID,<br>
                CustLocation, CustAccountBalance,<br>
                TransactionDate, TransactionAmount (INR)
            </code>
        </div>
        <div class="glass rounded-2xl p-5 border border-purple-500/20">
            <h3 class="font-semibold text-purple-300 mb-3 flex items-center gap-2">
                <i class="fas fa-file-csv"></i> Standart Format
            </h3>
            <p class="text-xs text-slate-400 mb-2">Kendi CSV'niz için:</p>
            <code class="text-xs text-slate-300 bg-black/30 p-2 rounded-lg block leading-relaxed">
                date, description,<br>
                amount, category,<br>
                location
            </code>
        </div>
    </div>

    {{-- Upload Form --}}
    <div class="glass rounded-2xl p-8">
        <form action="{{ route('transactions.import.csv') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div id="dropzone"
                 class="border-2 border-dashed border-white/20 rounded-2xl p-12 text-center cursor-pointer hover:border-indigo-500/50 hover:bg-indigo-500/5 transition-all"
                 onclick="document.getElementById('csv_file').click()">
                <i class="fas fa-cloud-upload-alt text-4xl text-slate-500 mb-4 block"></i>
                <p class="text-white font-medium mb-1">CSV dosyasını buraya sürükleyin veya tıklayın</p>
                <p class="text-slate-500 text-sm">Maksimum 50MB • .csv veya .txt</p>
                <p id="file-name" class="text-indigo-400 text-sm mt-2 hidden"></p>
            </div>

            <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" class="hidden"
                   onchange="document.getElementById('file-name').textContent = this.files[0].name; document.getElementById('file-name').classList.remove('hidden')">

            @error('csv_file')
            <p class="text-red-400 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror

            <div class="mt-6 p-4 rounded-xl bg-yellow-500/10 border border-yellow-500/20">
                <p class="text-yellow-300 text-sm font-medium mb-2"><i class="fas fa-lightbulb mr-2"></i>Veri Temizleme Adımları:</p>
                <ul class="text-slate-400 text-xs space-y-1">
                    <li>✓ Boş açıklamalar "Açıklama yok" ile değiştirilir</li>
                    <li>✓ Büyük/küçük harf normalize edilir (Title Case)</li>
                    <li>✓ Gereksiz karakterler (!, @, #, vb.) silinir</li>
                    <li>✓ Tarih formatları otomatik tespit edilir (d/m/y, Y-m-d, vb.)</li>
                    <li>✓ Tekrarlayan kayıtlar atlanır (TransactionID kontrolü)</li>
                </ul>
            </div>

            <button type="submit" id="upload-btn"
                    class="w-full mt-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-medium py-3 rounded-xl transition-all flex items-center justify-center gap-2">
                <i class="fas fa-upload"></i> Yükle ve İşle
            </button>
        </form>
    </div>

    {{-- Büyük veri uyarısı --}}
    <div class="glass rounded-2xl p-5 border border-blue-500/20">
        <p class="text-blue-300 text-sm"><i class="fas fa-info-circle mr-2"></i>
            <strong>Büyük Dosyalar:</strong> Bank_transactions.csv ~1 milyon satır içeriyor. 
            PHP memory_limit ve max_execution_time değerlerini .env veya php.ini'de artırmanız gerekebilir.
            Alternatif olarak önce küçük bir bölümü test edin.
        </p>
    </div>
</div>

@push('scripts')
<script>
const dropzone = document.getElementById('dropzone');
dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('border-indigo-500'); });
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('border-indigo-500'));
dropzone.addEventListener('drop', e => {
    e.preventDefault();
    dropzone.classList.remove('border-indigo-500');
    const file = e.dataTransfer.files[0];
    if (file) {
        document.getElementById('csv_file').files = e.dataTransfer.files;
        document.getElementById('file-name').textContent = file.name;
        document.getElementById('file-name').classList.remove('hidden');
    }
});

document.querySelector('form').addEventListener('submit', () => {
    const btn = document.getElementById('upload-btn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>İşleniyor...';
    btn.disabled = true;
});
</script>
@endpush
@endsection
