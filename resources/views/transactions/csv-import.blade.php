<x-app-layout>
    <x-slot name="header">CSV Yükle</x-slot>

    <div style="max-width:680px;margin:0 auto;">

        @if(session('success'))
            <div style="background:rgba(74,222,128,0.08);border:1px solid rgba(74,222,128,0.25);color:#4ade80;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:13px;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div style="background:rgba(245,200,66,0.08);border:1px solid rgba(245,200,66,0.25);color:#f5c842;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:13px;">
                {{ session('info') }}
            </div>
        @endif

        {{-- HEADER CARD --}}
        <div class="card" style="margin-bottom:16px;padding:28px 32px;position:relative;overflow:hidden;">
            <div style="position:absolute;top:-40px;right:-40px;width:180px;height:180px;background:radial-gradient(circle,rgba(201,243,29,0.06) 0%,transparent 70%);pointer-events:none;"></div>
            <div style="display:flex;align-items:center;gap:16px;">
                <div style="width:48px;height:48px;background:rgba(201,243,29,0.1);border:1px solid rgba(201,243,29,0.2);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="22" height="22" fill="none" stroke="#c9f31d" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <path d="M8 13h2m-2 4h6"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:17px;font-weight:600;color:#e2e2e8;margin-bottom:4px;">CSV Dosyası Yükle</div>
                    <div style="font-size:13px;color:var(--muted);">Banka işlem verilerinizi içe aktarın</div>
                </div>
            </div>
        </div>

        {{-- FORMAT BİLGİSİ --}}
        <div class="card" style="margin-bottom:16px;padding:20px 24px;">
            <div style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:14px;">Beklenen Format</div>
            <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px 16px;font-family:'DM Mono',monospace;font-size:12px;color:#a78bfa;overflow-x:auto;white-space:nowrap;">
                TransactionID, CustomerID, DOB, Gender, Location, Balance, Date, Time, Amount
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:14px;">
                @foreach([
                    ['TransactionID','İşlem kimliği','#c9f31d'],
                    ['CustomerID','Müşteri kimliği','#c9f31d'],
                    ['DOB','Doğum tarihi (d/m/Y)','#6b6b80'],
                    ['Gender','M / F / T','#6b6b80'],
                    ['Location','Şehir adı','#6b6b80'],
                    ['Balance','Hesap bakiyesi','#4ade80'],
                    ['Date','İşlem tarihi','#6b6b80'],
                    ['Time','İşlem saati','#6b6b80'],
                    ['Amount','İşlem tutarı','#4ade80'],
                ] as [$col, $desc, $color])
                <div style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:8px 12px;">
                    <div style="font-family:'DM Mono',monospace;font-size:11px;color:{{ $color }};margin-bottom:2px;">{{ $col }}</div>
                    <div style="font-size:11px;color:var(--muted);">{{ $desc }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- UPLOAD FORM --}}
        <div class="card" style="padding:28px 32px;">
            <form method="POST" action="{{ route('transactions.csvImport') }}" enctype="multipart/form-data" id="csvForm">
                @csrf

                {{-- DROP ZONE --}}
                <div id="dropZone"
                    style="border:2px dashed rgba(255,255,255,0.1);border-radius:14px;padding:48px 32px;text-align:center;cursor:pointer;transition:all 0.2s;margin-bottom:20px;"
                    onclick="document.getElementById('csvFileInput').click()"
                    ondragover="event.preventDefault();this.style.borderColor='#c9f31d';this.style.background='rgba(201,243,29,0.04)'"
                    ondragleave="this.style.borderColor='rgba(255,255,255,0.1)';this.style.background='transparent'"
                    ondrop="handleDrop(event)">
                    <div id="dropIcon" style="margin:0 auto 16px;width:52px;height:52px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:14px;display:flex;align-items:center;justify-content:center;">
                        <svg width="24" height="24" fill="none" stroke="var(--muted)" viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                        </svg>
                    </div>
                    <div style="font-size:14px;font-weight:500;color:#e2e2e8;margin-bottom:6px;" id="dropTitle">Sürükle & bırak ya da tıkla</div>
                    <div style="font-size:12px;color:var(--muted);">CSV veya TXT — maksimum 100MB</div>
                </div>

                <input type="file" id="csvFileInput" name="csv_file" accept=".csv,.txt" style="display:none" onchange="handleFileSelect(this)">

                @error('csv_file')
                    <div style="color:#f87171;font-size:12px;margin-bottom:14px;padding:10px 14px;background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.2);border-radius:8px;">
                        {{ $message }}
                    </div>
                @enderror

                {{-- DOSYA BİLGİSİ --}}
                <div id="fileInfo" style="display:none;background:var(--surface2);border:1px solid rgba(201,243,29,0.2);border-radius:10px;padding:12px 16px;margin-bottom:20px;display:none;align-items:center;gap:12px;">
                    <div style="width:36px;height:36px;background:rgba(201,243,29,0.1);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="16" height="16" fill="none" stroke="#c9f31d" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div id="fileName" style="font-size:13px;font-weight:500;color:#e2e2e8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>
                        <div id="fileSize" style="font-size:11px;color:var(--muted);margin-top:2px;"></div>
                    </div>
                    <button type="button" onclick="clearFile()" style="background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;line-height:0;transition:color 0.15s;" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--muted)'">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                {{-- UYARI --}}
                <div style="background:rgba(245,200,66,0.06);border:1px solid rgba(245,200,66,0.15);border-radius:10px;padding:12px 16px;margin-bottom:24px;display:flex;gap:10px;align-items:flex-start;">
                    <svg width="16" height="16" fill="none" stroke="#f5c842" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <div style="font-size:12px;color:#f5c842;line-height:1.6;">
                        100.000 satırdan fazla dosyalar arka planda işlenir. Yönlendirme sonrası birkaç dakika bekleyin.
                    </div>
                </div>

                {{-- BUTONLAR --}}
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn-primary" id="submitBtn" style="flex:1;justify-content:center;" disabled>
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                        </svg>
                        Yüklemeyi Başlat
                    </button>
                    <a href="{{ route('analytics') }}" class="btn-secondary">İptal</a>
                </div>
            </form>
        </div>

    </div>

    <script>
    function handleDrop(e) {
        e.preventDefault();
        const zone = document.getElementById('dropZone');
        zone.style.borderColor = 'rgba(255,255,255,0.1)';
        zone.style.background  = 'transparent';
        const file = e.dataTransfer.files[0];
        if (file) setFile(file);
    }

    function handleFileSelect(input) {
        if (input.files[0]) setFile(input.files[0]);
    }

    function setFile(file) {
        // dosya bilgisini göster
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatBytes(file.size);
        const info = document.getElementById('fileInfo');
        info.style.display = 'flex';

        // drop zone güncelle
        document.getElementById('dropTitle').textContent = '✓ Dosya seçildi';
        document.getElementById('dropZone').style.borderColor = 'rgba(201,243,29,0.4)';

        // submit aktif
        const btn = document.getElementById('submitBtn');
        btn.disabled = false;
        btn.style.opacity = '1';

        // input'a dosyayı aktar (drag-drop için)
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('csvFileInput').files = dt.files;
    }

    function clearFile() {
        document.getElementById('csvFileInput').value = '';
        document.getElementById('fileInfo').style.display = 'none';
        document.getElementById('dropTitle').textContent = 'Sürükle & bırak ya da tıkla';
        document.getElementById('dropZone').style.borderColor = 'rgba(255,255,255,0.1)';
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.style.opacity = '0.5';
    }

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    // Submit sırasında loading göster
    document.getElementById('csvForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation:spin 1s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Yükleniyor...';
        btn.disabled = true;
    });

    // Submit butonunun başlangıç stili
    document.getElementById('submitBtn').style.opacity = '0.5';
    </script>

    <style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>

</x-app-layout>