<x-app-layout>
    <x-slot name="header">CSV Analiz</x-slot>
    <x-slot name="topbarActions">
        <button onclick="document.getElementById('importModal').style.display='flex'" class="btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>
            </svg>
            CSV Yükle
        </button>
    </x-slot>

    <style>
        .analytics-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .analytics-grid-2-1 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .analytics-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .chart-card {
            background: var(--card, #12121a);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 14px;
            padding: 20px 24px 24px;
        }
        .chart-title {
            font-size: 13px;
            font-weight: 600;
            color: #e2e2e8;
            margin-bottom: 3px;
            letter-spacing: 0.01em;
        }
        .chart-subtitle {
            font-size: 11px;
            color: var(--muted, #6b6b80);
            margin-bottom: 20px;
        }
        .chart-wrap {
            position: relative;
        }
        .stat-card-pro {
            background: var(--card, #12121a);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 14px;
            padding: 20px 22px;
            position: relative;
            overflow: hidden;
            transition: border-color 0.2s;
        }
        .stat-card-pro:hover { border-color: rgba(255,255,255,0.14); }
        .stat-card-pro .accent-bar {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
        }
        .stat-label-pro {
            font-size: 11px;
            font-weight: 500;
            color: var(--muted, #6b6b80);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
        }
        .stat-value-pro {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
            font-family: 'DM Mono', monospace;
        }
        .stat-badge {
            display: inline-block;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 500;
        }
        .flash-success {
            background: rgba(74,222,128,0.08);
            border: 1px solid rgba(74,222,128,0.25);
            color: #4ade80;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 13px;
        }
        .flash-info {
            background: rgba(245,200,66,0.08);
            border: 1px solid rgba(245,200,66,0.25);
            color: #f5c842;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 13px;
        }
        .last-update {
            text-align: right;
            color: var(--muted, #6b6b80);
            font-size: 11px;
            margin-top: 12px;
            letter-spacing: 0.03em;
        }
    </style>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flash-success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="flash-info">{{ session('info') }}</div>
    @endif

    @if($stats['total_transactions'] === 0)
        <div class="chart-card" style="text-align:center;padding:80px 40px;">
            <svg width="56" height="56" fill="none" stroke="var(--muted)" viewBox="0 0 24 24" style="margin:0 auto 16px;">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            <div style="font-size:17px;font-weight:600;margin-bottom:8px;color:#e2e2e8;">Henüz veri yok</div>
            <div style="color:var(--muted);font-size:13px;margin-bottom:24px;">bank_transactions.csv dosyanızı yükleyerek analize başlayın</div>
            <button onclick="document.getElementById('importModal').style.display='flex'" class="btn-primary" style="display:inline-flex;">
                CSV Yükle
            </button>
        </div>
    @else

    {{-- STAT CARDS --}}
    <div class="analytics-grid-4">
        <div class="stat-card-pro">
            <div class="accent-bar" style="background:linear-gradient(90deg,#c9f31d,rgba(201,243,29,0));"></div>
            <div class="stat-label-pro">Toplam İşlem</div>
            <div class="stat-value-pro" style="color:#c9f31d;">{{ number_format($stats['total_transactions']) }}</div>
            <span class="stat-badge" style="background:rgba(201,243,29,0.1);color:#c9f31d;">kayıt</span>
        </div>
        <div class="stat-card-pro">
            <div class="accent-bar" style="background:linear-gradient(90deg,#4ade80,rgba(74,222,128,0));"></div>
            <div class="stat-label-pro">Toplam Müşteri</div>
            <div class="stat-value-pro" style="color:#4ade80;">{{ number_format($stats['total_customers']) }}</div>
            <span class="stat-badge" style="background:rgba(74,222,128,0.1);color:#4ade80;">benzersiz</span>
        </div>
        <div class="stat-card-pro">
            <div class="accent-bar" style="background:linear-gradient(90deg,#a78bfa,rgba(167,139,250,0));"></div>
            <div class="stat-label-pro">Toplam Hacim</div>
            <div class="stat-value-pro" style="color:#a78bfa;">₹{{ number_format($stats['total_amount'], 0) }}</div>
            <span class="stat-badge" style="background:rgba(167,139,250,0.1);color:#a78bfa;">INR</span>
        </div>
        <div class="stat-card-pro">
            <div class="accent-bar" style="background:linear-gradient(90deg,#f87171,rgba(248,113,113,0));"></div>
            <div class="stat-label-pro">Ort. İşlem</div>
            <div class="stat-value-pro" style="color:#f87171;">₹{{ number_format($stats['avg_amount'], 0) }}</div>
            <span class="stat-badge" style="background:rgba(248,113,113,0.1);color:#f87171;">INR / işlem</span>
        </div>
    </div>

    {{-- CHARTS ROW 1 --}}
    <div class="analytics-grid-2-1">
        <div class="chart-card">
            <div class="chart-title">Aylık İşlem Hacmi</div>
            <div class="chart-subtitle">Tüm dönemler — aylık toplam</div>
            <div class="chart-wrap" style="height:220px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-title">Cinsiyet Dağılımı</div>
            <div class="chart-subtitle">Müşteri bazlı</div>
            <div class="chart-wrap" style="height:220px;">
                <canvas id="genderChart"></canvas>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW 2 --}}
    <div class="analytics-grid-2">
        <div class="chart-card">
            <div class="chart-title">En Çok İşlem Yapılan Şehirler</div>
            <div class="chart-subtitle">Top 10 — müşteri sayısına göre</div>
            <div class="chart-wrap" style="height:280px;">
                <canvas id="locationChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-title">Müşteri Bakiye Dağılımı</div>
            <div class="chart-subtitle">INR cinsinden aralıklar</div>
            <div class="chart-wrap" style="height:280px;">
                <canvas id="balanceChart"></canvas>
            </div>
        </div>
    </div>

    @if($lastImport)
    <div class="last-update">
        Son güncelleme: {{ \Carbon\Carbon::parse($lastImport)->format('d.m.Y H:i') }}
    </div>
    @endif

    @endif

    {{-- IMPORT MODAL --}}
    <div id="importModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
        <div class="chart-card" style="width:480px;padding:32px;position:relative;">
            <button onclick="document.getElementById('importModal').style.display='none'"
                style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,0.06);border:none;cursor:pointer;color:var(--muted);border-radius:8px;padding:6px;line-height:0;transition:background 0.2s;"
                onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                onmouseout="this.style.background='rgba(255,255,255,0.06)'">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>

            <div style="font-size:17px;font-weight:600;margin-bottom:6px;color:#e2e2e8;">CSV Dosyası Yükle</div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:24px;">
                Beklenen format:
                <code style="font-family:'DM Mono',monospace;font-size:11px;color:#a78bfa;background:rgba(167,139,250,0.1);padding:2px 6px;border-radius:4px;">
                    TransactionID, CustomerID, DOB, Gender, Location, Balance, Date, Time, Amount
                </code>
            </div>

            <form method="POST" action="{{ route('transactions.csvImport') }}" enctype="multipart/form-data">
                @csrf
                <div id="dropZone"
                    style="border:2px dashed rgba(255,255,255,0.12);border-radius:12px;padding:36px;text-align:center;margin-bottom:20px;cursor:pointer;transition:border-color 0.2s,background 0.2s;"
                    onclick="document.getElementById('csvFileInput').click()"
                    ondragover="event.preventDefault();this.style.borderColor='#a78bfa';this.style.background='rgba(167,139,250,0.05)'"
                    ondragleave="this.style.borderColor='rgba(255,255,255,0.12)';this.style.background='transparent'"
                    ondrop="handleDrop(event)">
                    <svg width="30" height="30" fill="none" stroke="var(--muted)" viewBox="0 0 24 24" style="margin:0 auto 12px;display:block;">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                    </svg>
                    <div style="font-size:13px;color:var(--muted);" id="dropText">Sürükle & bırak ya da tıkla</div>
                    <div style="font-size:11px;color:var(--muted);margin-top:4px;opacity:0.6;">CSV, TXT — max 100MB</div>
                </div>

                <input type="file" id="csvFileInput" name="csv_file" accept=".csv,.txt" style="display:none" onchange="updateDropText(this)">

                @error('csv_file')
                    <div style="color:#f87171;font-size:12px;margin-bottom:12px;">{{ $message }}</div>
                @enderror

                <div style="background:rgba(245,200,66,0.06);border:1px solid rgba(245,200,66,0.18);border-radius:8px;padding:10px 14px;margin-bottom:20px;font-size:12px;color:#f5c842;">
                    ⚠ 100.000 satırdan fazla dosyalar arka planda işlenir.
                </div>

                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">
                    Yüklemeyi Başlat
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    function handleDrop(e) {
        e.preventDefault();
        const zone = document.getElementById('dropZone');
        zone.style.borderColor = 'rgba(255,255,255,0.12)';
        zone.style.background = 'transparent';
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById('csvFileInput').files = dt.files;
            document.getElementById('dropText').textContent = '📄 ' + file.name;
        }
    }

    function updateDropText(input) {
        if (input.files[0]) {
            document.getElementById('dropText').textContent = '📄 ' + input.files[0].name;
        }
    }

    @if($stats['total_transactions'] > 0)

    Chart.defaults.font.family = "'DM Mono', monospace";
    Chart.defaults.color = '#6b6b80';

    const gridColor  = 'rgba(255,255,255,0.05)';
    const tickColor  = '#6b6b80';
    const tickFont   = { size: 11 };
    const borderColor = 'rgba(255,255,255,0.07)';

    const scalesXY = {
        x: { grid: { color: gridColor }, ticks: { color: tickColor, font: tickFont }, border: { color: borderColor } },
        y: { grid: { color: gridColor }, ticks: { color: tickColor, font: tickFont }, border: { color: borderColor } }
    };

    // 1. Aylık işlem hacmi — line chart
    const monthlyLabels = @json($monthlyData->pluck('month'));
    const monthlyTotals = @json($monthlyData->pluck('total'));

    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Toplam Hacim',
                data: monthlyTotals,
                borderColor: '#c9f31d',
                backgroundColor: 'rgba(201,243,29,0.07)',
                borderWidth: 2,
                pointRadius: monthlyLabels.length > 24 ? 0 : 3,
                pointHoverRadius: 5,
                pointBackgroundColor: '#c9f31d',
                fill: true,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1a28',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: ctx => ' ₹' + Number(ctx.raw).toLocaleString('tr-TR')
                    }
                }
            },
            scales: scalesXY
        }
    });

    // 2. Cinsiyet dağılımı — doughnut
    const genderRaw = @json($genderData);
    const genderLabels = Object.keys(genderRaw);
    const genderValues = Object.values(genderRaw);

    new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
            labels: genderLabels,
            datasets: [{
                data: genderValues,
                backgroundColor: [
                    'rgba(201,243,29,0.75)',
                    'rgba(248,113,113,0.75)',
                    'rgba(167,139,250,0.75)',
                    'rgba(245,200,66,0.75)',
                ],
                borderColor: '#12121a',
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        color: '#9090a8',
                        font: { size: 11 },
                        padding: 14,
                        usePointStyle: true,
                        pointStyleWidth: 8,
                    }
                },
                tooltip: {
                    backgroundColor: '#1a1a28',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${Number(ctx.raw).toLocaleString('tr-TR')}`
                    }
                }
            }
        }
    });

    // 3. Şehir bazlı — horizontal bar
    const locationLabels = @json($locationData->pluck('location'));
    const locationCounts = @json($locationData->pluck('count'));

    new Chart(document.getElementById('locationChart'), {
        type: 'bar',
        data: {
            labels: locationLabels,
            datasets: [{
                label: 'Müşteri Sayısı',
                data: locationCounts,
                backgroundColor: locationLabels.map((_, i) =>
                    `rgba(167,139,250,${0.85 - i * 0.06})`
                ),
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1a28',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: ctx => ` ${Number(ctx.raw).toLocaleString('tr-TR')} müşteri`
                    }
                }
            },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: tickColor, font: tickFont }, border: { color: borderColor } },
                y: { grid: { color: 'transparent' }, ticks: { color: '#b0b0c8', font: { size: 11 } }, border: { color: borderColor } }
            }
        }
    });

    // 4. Bakiye aralıkları — bar chart
    const balanceRanges = @json($balanceRanges);
    const rangeOrder = ['< 1K', '1K - 10K', '10K - 100K', '100K - 1M', '> 1M'];
    const rangeColors = [
        'rgba(248,113,113,0.8)',
        'rgba(245,200,66,0.8)',
        'rgba(74,222,128,0.8)',
        'rgba(201,243,29,0.8)',
        'rgba(167,139,250,0.8)',
    ];

    new Chart(document.getElementById('balanceChart'), {
        type: 'bar',
        data: {
            labels: rangeOrder,
            datasets: [{
                label: 'Müşteri Sayısı',
                data: rangeOrder.map(k => balanceRanges[k] || 0),
                backgroundColor: rangeColors,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1a28',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: ctx => ` ${Number(ctx.raw).toLocaleString('tr-TR')} müşteri`
                    }
                }
            },
            scales: scalesXY
        }
    });

    @endif
    </script>
</x-app-layout>