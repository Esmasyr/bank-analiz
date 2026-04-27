@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">

    {{-- Başlık --}}
    <div>
        <h2 class="text-3xl font-bold gradient-text">Finansal Analiz</h2>
        <p class="text-slate-400 mt-1">Son 30 günlük aktivite özeti</p>
    </div>

    {{-- ÖZET KARTLAR --}}
    <div class="grid grid-cols-4 gap-6">
        <div class="glass rounded-2xl p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-400 text-sm">Toplam Gelir</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center">
                    <i class="fas fa-arrow-up text-emerald-400"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-emerald-400">{{ number_format($summary['total_income'], 0) }} ₹</p>
            <p class="text-xs text-slate-500 mt-1">{{ $summary['tx_count'] }} işlem</p>
        </div>

        <div class="glass rounded-2xl p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-400 text-sm">Toplam Gider</span>
                <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center">
                    <i class="fas fa-arrow-down text-red-400"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-red-400">{{ number_format($summary['total_expense'], 0) }} ₹</p>
            <p class="text-xs text-slate-500 mt-1">Ortalama {{ number_format($summary['avg_transaction'], 0) }} ₹</p>
        </div>

        <div class="glass rounded-2xl p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-400 text-sm">Net Bakiye</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center">
                    <i class="fas fa-wallet text-indigo-400"></i>
                </div>
            </div>
            <p class="text-2xl font-bold {{ $summary['net_balance'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                {{ number_format($summary['net_balance'], 0) }} ₹
            </p>
            <p class="text-xs text-slate-500 mt-1">Tasarruf oranı: {{ $summary['savings_rate'] }}%</p>
        </div>

        <div class="glass rounded-2xl p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-400 text-sm">Sonraki Ay Tahmini</span>
                <div class="w-10 h-10 rounded-xl bg-purple-500/20 flex items-center justify-center">
                    <i class="fas fa-crystal-ball text-purple-400"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-purple-400">{{ number_format($forecast['forecast'], 0) }} ₹</p>
            <p class="text-xs text-slate-500 mt-1">
                Güven: 
                <span class="{{ $forecast['confidence'] === 'high' ? 'text-emerald-400' : ($forecast['confidence'] === 'medium' ? 'text-yellow-400' : 'text-red-400') }}">
                    {{ ucfirst($forecast['confidence']) }}
                </span>
            </p>
        </div>
    </div>

    {{-- GRAFIKLER --}}
    <div class="grid grid-cols-2 gap-6">
        {{-- Aylık Trend --}}
        <div class="glass rounded-2xl p-6">
            <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-chart-area text-indigo-400"></i> Aylık Trend
                @if($monthly['trend'] !== null)
                    <span class="text-xs px-2 py-1 rounded-full {{ $monthly['trend'] > 0 ? 'bg-red-500/20 text-red-400' : 'bg-emerald-500/20 text-emerald-400' }}">
                        {{ $monthly['trend'] > 0 ? '↑' : '↓' }} {{ abs($monthly['trend']) }}%
                    </span>
                @endif
            </h3>
            <canvas id="monthlyChart" height="200"></canvas>
        </div>

        {{-- Kategori Dağılımı --}}
        <div class="glass rounded-2xl p-6">
            <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-chart-pie text-purple-400"></i> Kategori Dağılımı
            </h3>
            <div class="flex items-center gap-6">
                <canvas id="categoryChart" width="200" height="200"></canvas>
                <div class="flex-1 space-y-2">
                    @foreach($topCats->take(5) as $cat)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-300 text-sm capitalize">{{ $cat->category }}</span>
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-white/10 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full bg-indigo-500" style="width: {{ $cat->percentage }}%"></div>
                            </div>
                            <span class="text-xs text-slate-400">{{ $cat->percentage }}%</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Haftalık Pattern + Anomaliler --}}
    <div class="grid grid-cols-3 gap-6">
        {{-- Haftalık harcama --}}
        <div class="glass rounded-2xl p-6 col-span-2">
            <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-calendar-week text-cyan-400"></i> Haftalık Harcama Paterni
            </h3>
            <canvas id="weeklyChart" height="150"></canvas>
        </div>

        {{-- Anomaliler --}}
        <div class="glass rounded-2xl p-6">
            <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i> Anomaliler
                <span class="text-xs bg-yellow-500/20 text-yellow-400 px-2 py-0.5 rounded-full">{{ $anomalies->count() }}</span>
            </h3>
            @if($anomalies->isEmpty())
                <p class="text-slate-400 text-sm">Anormal işlem bulunamadı ✓</p>
            @else
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @foreach($anomalies->take(10) as $tx)
                    <div class="p-3 rounded-xl bg-yellow-500/10 border border-yellow-500/20">
                        <p class="text-yellow-300 font-semibold text-sm">{{ number_format(abs($tx->amount), 0) }} ₹</p>
                        <p class="text-slate-400 text-xs">{{ $tx->description ?? 'Açıklama yok' }}</p>
                        <p class="text-slate-500 text-xs mt-1">Z-score: {{ $tx->z_score }}</p>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Tahmin Detayı --}}
    @if(isset($forecast['monthly_data']) && count($forecast['monthly_data']) > 0)
    <div class="glass rounded-2xl p-6">
        <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
            <i class="fas fa-brain text-purple-400"></i> Tahmin Analizi
            <span class="text-xs text-slate-400 font-normal ml-2">Ağırlıklı hareketli ortalama + lineer regresyon</span>
        </h3>
        <canvas id="forecastChart" height="100"></canvas>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
const chartDefaults = {
    responsive: true,
    plugins: { legend: { labels: { color: '#94a3b8', font: { family: 'system-ui' } } } }
};

// ─── Aylık Trend Grafiği ───────────────────────────────
const monthlyData = @json($monthly['data']);
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: monthlyData.map(d => d.month),
        datasets: [
            {
                label: 'Gelir',
                data: monthlyData.map(d => d.income),
                backgroundColor: 'rgba(16, 185, 129, 0.6)',
                borderColor: '#10b981',
                borderWidth: 1,
                borderRadius: 4,
            },
            {
                label: 'Gider',
                data: monthlyData.map(d => d.expense),
                backgroundColor: 'rgba(244, 63, 94, 0.6)',
                borderColor: '#f43f5e',
                borderWidth: 1,
                borderRadius: 4,
            }
        ]
    },
    options: {
        ...chartDefaults,
        scales: {
            x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } },
            y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } }
        }
    }
});

// ─── Kategori Pasta Grafiği ────────────────────────────
const catData = @json($topCats);
const catColors = ['#6366f1','#a855f7','#ec4899','#f43f5e','#f97316','#eab308','#10b981','#06b6d4'];
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: catData.map(c => c.category),
        datasets: [{
            data: catData.map(c => c.total),
            backgroundColor: catColors,
            borderWidth: 0,
            hoverOffset: 4,
        }]
    },
    options: {
        ...chartDefaults,
        cutout: '70%',
        plugins: { legend: { display: false } }
    }
});

// ─── Haftalık Pattern ─────────────────────────────────
const weeklyData = @json($weekly);
const dayNames = ['Paz','Pzt','Sal','Çar','Per','Cum','Cmt'];
new Chart(document.getElementById('weeklyChart'), {
    type: 'line',
    data: {
        labels: weeklyData.map(d => d.day_name ?? dayNames[d.day_of_week - 1]),
        datasets: [{
            label: 'Ort. Harcama (₹)',
            data: weeklyData.map(d => d.avg_amount),
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#6366f1',
            pointRadius: 4,
        }]
    },
    options: {
        ...chartDefaults,
        scales: {
            x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } },
            y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } }
        }
    }
});

// ─── Tahmin Grafiği ────────────────────────────────────
@if(isset($forecast['monthly_data']) && count($forecast['monthly_data']) > 0)
const forecastData = @json($forecast['monthly_data']);
const months = Object.keys(forecastData);
const values = Object.values(forecastData);
const forecastVal = {{ $forecast['forecast'] }};

new Chart(document.getElementById('forecastChart'), {
    type: 'line',
    data: {
        labels: [...months, 'Tahmin'],
        datasets: [
            {
                label: 'Gerçek Harcama',
                data: [...values, null],
                borderColor: '#f43f5e',
                backgroundColor: 'rgba(244,63,94,0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
            },
            {
                label: 'Tahmin',
                data: [...values.map(() => null), forecastVal],
                borderColor: '#a855f7',
                borderDash: [5, 5],
                pointBackgroundColor: '#a855f7',
                pointRadius: 8,
                pointStyle: 'star',
            }
        ]
    },
    options: {
        ...chartDefaults,
        scales: {
            x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } },
            y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } }
        }
    }
});
@endif
</script>
@endpush
