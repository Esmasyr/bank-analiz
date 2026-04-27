<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>
    <x-slot name="topbarActions">
        <a href="{{ route('transactions.create') }}" class="btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Yeni İşlem
        </a>
    </x-slot>

    <style>
        .dash-grid-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 20px; }
        .dash-grid-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; margin-bottom: 20px; }
        .dash-grid-main { display: grid; grid-template-columns: 1fr 360px; gap: 16px; }
        .kpi-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 20px; position: relative; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
        .kpi-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; }
        .kpi-card.lime::before { background: linear-gradient(90deg, var(--accent), transparent); }
        .kpi-card.green::before { background: linear-gradient(90deg, #4ade80, transparent); }
        .kpi-card.red::before { background: linear-gradient(90deg, #f87171, transparent); }
        .kpi-card.purple::before { background: linear-gradient(90deg, #a78bfa, transparent); }
        .kpi-label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px; }
        .kpi-value { font-size: 22px; font-weight: 700; font-family: 'DM Mono', monospace; margin-bottom: 6px; line-height: 1; }
        .kpi-value.lime { color: var(--accent); }
        .kpi-value.green { color: #4ade80; }
        .kpi-value.red { color: #f87171; }
        .kpi-value.purple { color: #a78bfa; }
        .kpi-sub { font-size: 11px; color: var(--muted); }
        .kpi-icon { position: absolute; top: 16px; right: 16px; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .status-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 18px 20px; display: flex; align-items: center; gap: 14px; transition: transform 0.2s; }
        .status-card:hover { transform: translateY(-1px); }
        .status-icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .status-num { font-size: 26px; font-weight: 700; font-family: 'DM Mono', monospace; line-height: 1; }
        .status-lbl { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 1.2px; margin-top: 3px; }
        .chart-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 24px; }
        .chart-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .chart-title { font-size: 14px; font-weight: 600; color: var(--text); }
        .chart-sub { font-size: 11px; color: var(--muted); margin-top: 3px; }
        .chart-legend { display: flex; gap: 14px; }
        .legend-item { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--muted); }
        .legend-dot { width: 8px; height: 8px; border-radius: 2px; }
        .recent-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 24px; display: flex; flex-direction: column; }
        .recent-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .recent-title { font-size: 14px; font-weight: 600; }
        .recent-link { font-size: 12px; color: var(--accent); text-decoration: none; }
        .recent-link:hover { text-decoration: underline; }
        .tx-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 10px; background: var(--surface2); text-decoration: none; transition: background 0.15s, transform 0.15s; margin-bottom: 8px; }
        .tx-item:last-child { margin-bottom: 0; }
        .tx-item:hover { background: rgba(255,255,255,0.05); transform: translateX(2px); }
        .tx-avatar { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .tx-ref { font-size: 12px; font-family: 'DM Mono', monospace; color: var(--text); }
        .tx-date { font-size: 11px; color: var(--muted); margin-top: 2px; }
        .tx-amount { font-size: 13px; font-weight: 600; font-family: 'DM Mono', monospace; text-align: right; }
        .tx-badge { font-size: 10px; padding: 2px 7px; border-radius: 4px; display: inline-block; margin-top: 3px; }
    </style>

    {{-- KPI CARDS --}}
    <div class="dash-grid-4">
        <div class="kpi-card lime">
            <div class="kpi-icon" style="background:rgba(201,243,29,0.1);">
                <svg width="18" height="18" fill="none" stroke="var(--accent)" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <div class="kpi-label">Net Bakiye</div>
            <div class="kpi-value lime">₺{{ number_format($stats['balance'], 2) }}</div>
            <div class="kpi-sub">Tamamlanan işlemler</div>
        </div>
        <div class="kpi-card green">
            <div class="kpi-icon" style="background:rgba(74,222,128,0.1);">
                <svg width="18" height="18" fill="none" stroke="#4ade80" viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
            </div>
            <div class="kpi-label">Toplam Giriş</div>
            <div class="kpi-value green">₺{{ number_format($stats['total_income'], 2) }}</div>
            <div class="kpi-sub">Tamamlanan</div>
        </div>
        <div class="kpi-card red">
            <div class="kpi-icon" style="background:rgba(248,113,113,0.1);">
                <svg width="18" height="18" fill="none" stroke="#f87171" viewBox="0 0 24 24"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            </div>
            <div class="kpi-label">Toplam Çıkış</div>
            <div class="kpi-value red">₺{{ number_format($stats['total_expense'], 2) }}</div>
            <div class="kpi-sub">Tamamlanan</div>
        </div>
        <div class="kpi-card purple">
            <div class="kpi-icon" style="background:rgba(167,139,250,0.1);">
                <svg width="18" height="18" fill="none" stroke="#a78bfa" viewBox="0 0 24 24"><path d="M7 16l-4-4m0 0l4-4m-4 4h18M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </div>
            <div class="kpi-label">Toplam Transfer</div>
            <div class="kpi-value purple">₺{{ number_format($stats['total_transfer'], 2) }}</div>
            <div class="kpi-sub">Tamamlanan</div>
        </div>
    </div>

    {{-- STATUS ROW --}}
    <div class="dash-grid-3">
        <div class="status-card">
            <div class="status-icon" style="background:rgba(245,200,66,0.1);">
                <svg width="20" height="20" fill="none" stroke="#f5c842" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </div>
            <div>
                <div class="status-num" style="color:#f5c842;">{{ number_format($stats['pending']) }}</div>
                <div class="status-lbl">Bekleyen</div>
            </div>
        </div>
        <div class="status-card">
            <div class="status-icon" style="background:rgba(74,222,128,0.1);">
                <svg width="20" height="20" fill="none" stroke="#4ade80" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <div class="status-num" style="color:#4ade80;">{{ number_format($stats['completed']) }}</div>
                <div class="status-lbl">Tamamlanan</div>
            </div>
        </div>
        <div class="status-card">
            <div class="status-icon" style="background:rgba(248,113,113,0.1);">
                <svg width="20" height="20" fill="none" stroke="#f87171" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div>
                <div class="status-num" style="color:#f87171;">{{ number_format($stats['failed']) }}</div>
                <div class="status-lbl">Başarısız</div>
            </div>
        </div>
    </div>

    {{-- CHART + RECENT --}}
    <div class="dash-grid-main">
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Aylık İşlem Analizi</div>
                    <div class="chart-sub">Yıllık giriş & çıkış dağılımı</div>
                </div>
                <div class="chart-legend">
                    <div class="legend-item"><span class="legend-dot" style="background:var(--accent);"></span> Giriş</div>
                    <div class="legend-item"><span class="legend-dot" style="background:#f87171;"></span> Çıkış</div>
                </div>
            </div>
            <canvas id="chart"></canvas>
        </div>

        <div class="recent-card">
            <div class="recent-header">
                <div class="recent-title">Son İşlemler</div>
                <a href="{{ route('transactions.index') }}" class="recent-link">Tümü →</a>
            </div>
            <div style="flex:1;">
                @forelse($recentTransactions as $t)
                <a href="{{ route('transactions.show', $t) }}" class="tx-item">
                    <div class="tx-avatar" style="background:{{ $t->type === 'deposit' ? 'rgba(74,222,128,0.1)' : ($t->type === 'withdrawal' ? 'rgba(248,113,113,0.1)' : 'rgba(167,139,250,0.1)') }};">
                        @if($t->type === 'deposit')
                            <svg width="15" height="15" fill="none" stroke="#4ade80" viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                        @elseif($t->type === 'withdrawal')
                            <svg width="15" height="15" fill="none" stroke="#f87171" viewBox="0 0 24 24"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                        @else
                            <svg width="15" height="15" fill="none" stroke="#a78bfa" viewBox="0 0 24 24"><path d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
                        @endif
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="tx-ref">{{ $t->reference_number }}</div>
                        <div class="tx-date">{{ $t->processed_at ? \Carbon\Carbon::parse($t->processed_at)->format('d.m.Y') : $t->created_at->format('d.m.Y') }}</div>
                    </div>
                    <div>
                        <div class="tx-amount" style="color:{{ $t->type === 'deposit' ? '#4ade80' : ($t->type === 'withdrawal' ? '#f87171' : '#a78bfa') }};">
                            {{ $t->type === 'withdrawal' ? '-' : '+' }}₺{{ number_format($t->amount, 0) }}
                        </div>
                        <span class="tx-badge" style="background:{{ $t->status === 'completed' ? 'rgba(74,222,128,0.1)' : ($t->status === 'pending' ? 'rgba(245,200,66,0.1)' : 'rgba(248,113,113,0.1)') }};color:{{ $t->status === 'completed' ? '#4ade80' : ($t->status === 'pending' ? '#f5c842' : '#f87171') }};">
                            {{ $t->status === 'completed' ? 'Tamam' : ($t->status === 'pending' ? 'Bekliyor' : 'Başarısız') }}
                        </span>
                    </div>
                </a>
                @empty
                <div style="text-align:center;color:var(--muted);padding:40px 0;font-size:14px;">Henüz işlem yok</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- CHATBOT --}}
    <div id="chatbot" style="position:fixed;bottom:24px;right:24px;z-index:9999;">
        <button onclick="toggleChat()" id="chat-toggle" style="width:52px;height:52px;border-radius:50%;background:var(--accent);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(201,243,29,0.4);transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
            <svg width="22" height="22" fill="none" stroke="#000" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        </button>
        <div id="chat-window" style="display:none;position:absolute;bottom:64px;right:0;width:320px;background:var(--surface);border:1px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.5);">
            <div style="padding:16px;background:var(--surface2);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(201,243,29,0.15);display:flex;align-items:center;justify-content:center;">
                    <svg width="16" height="16" fill="none" stroke="var(--accent)" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:600;">BankAnaliz AI</div>
                    <div style="font-size:11px;color:var(--muted);">Finansal asistanınız</div>
                </div>
            </div>
            <div id="chat-messages" style="height:280px;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;">
                <div style="background:var(--surface2);padding:10px 12px;border-radius:10px;font-size:13px;color:var(--muted);max-width:85%;">
                    Merhaba! Finansal verileriniz hakkında soru sorabilirsiniz 👋
                </div>
            </div>
            <div style="padding:12px;border-top:1px solid var(--border);display:flex;gap:8px;">
                <input id="chat-input" type="text" placeholder="Soru sorun..."
                    style="flex:1;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:8px 12px;font-size:13px;color:var(--text);outline:none;"
                    onkeydown="if(event.key==='Enter') sendMessage()">
                <button onclick="sendMessage()" style="width:36px;height:36px;border-radius:8px;background:var(--accent);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="16" height="16" fill="none" stroke="#000" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/></svg>
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('chart').getContext('2d');
    const labels = @json(array_column($monthlyData, 'month'));
    const incomes = @json(array_column($monthlyData, 'income'));
    const expenses = @json(array_column($monthlyData, 'expense'));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Giriş', data: incomes, backgroundColor: 'rgba(201,243,29,0.65)', hoverBackgroundColor: 'rgba(201,243,29,0.9)', borderRadius: 5, borderSkipped: false },
                { label: 'Çıkış', data: expenses, backgroundColor: 'rgba(248,113,113,0.65)', hoverBackgroundColor: 'rgba(248,113,113,0.9)', borderRadius: 5, borderSkipped: false }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,15,25,0.95)',
                    borderColor: 'rgba(255,255,255,0.08)',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: { label: ctx => ' ₺' + parseFloat(ctx.raw).toLocaleString('tr-TR', {minimumFractionDigits:0}) }
                }
            },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.03)' }, ticks: { color: '#6b6b80', font: { size: 11 } }, border: { color: 'rgba(255,255,255,0.06)' } },
                y: { grid: { color: 'rgba(255,255,255,0.03)' }, ticks: { color: '#6b6b80', font: { size: 11 }, callback: v => '₺' + (v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'K' : v) }, border: { color: 'rgba(255,255,255,0.06)' } }
            }
        }
    });

    function toggleChat() {
        const w = document.getElementById('chat-window');
        w.style.display = w.style.display === 'none' ? 'block' : 'none';
        if(w.style.display === 'block') document.getElementById('chat-input').focus();
    }

    function sendMessage() {
        const input = document.getElementById('chat-input');
        const msg = input.value.trim();
        if(!msg) return;
        addMessage(msg, 'user');
        input.value = '';
        addMessage('Yazıyor...', 'bot', 'typing');
        fetch('{{ route("chat.ask") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ message: msg })
        })
        .then(r => r.json())
        .then(data => { document.getElementById('typing')?.remove(); addMessage(data.reply, 'bot'); })
        .catch(() => { document.getElementById('typing')?.remove(); addMessage('Bir hata oluştu.', 'bot'); });
    }

    function addMessage(text, from, id=null) {
        const box = document.getElementById('chat-messages');
        const div = document.createElement('div');
        if(id) div.id = id;
        div.style.cssText = `padding:10px 12px;border-radius:10px;font-size:13px;max-width:85%;${from==='user'?'background:rgba(201,243,29,0.15);color:var(--text);align-self:flex-end;margin-left:auto;':'background:var(--surface2);color:var(--muted);'}`;
        div.textContent = text;
        box.appendChild(div);
        box.scrollTop = box.scrollHeight;
    }
    </script>
</x-app-layout>
