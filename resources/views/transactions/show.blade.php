{{-- SHOW VIEW --}}
<x-app-layout>
    <x-slot name="header">İşlem Detayı</x-slot>
    <x-slot name="topbarActions">
        <a href="{{ route('transactions.index') }}" class="btn-secondary">← Geri</a>
    </x-slot>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div style="max-width:640px;">
        <div class="card">
            {{-- HEADER --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div style="width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;
                        background:{{ $transaction->type==='income' ? 'rgba(74,222,128,0.1)' : ($transaction->type==='expense' ? 'rgba(248,113,113,0.1)' : 'rgba(167,139,250,0.1)') }};">
                        @if($transaction->type==='income')
                            <svg width="22" height="22" fill="none" stroke="#4ade80" viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                        @elseif($transaction->type==='expense')
                            <svg width="22" height="22" fill="none" stroke="#f87171" viewBox="0 0 24 24"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                        @else
                            <svg width="22" height="22" fill="none" stroke="#a78bfa" viewBox="0 0 24 24"><path d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
                        @endif
                    </div>
                    <div>
                        <div style="font-size:20px;font-weight:600;font-family:'DM Mono',monospace;
                            color:{{ $transaction->type==='income' ? '#4ade80' : ($transaction->type==='expense' ? '#f87171' : '#a78bfa') }};">
                            {{ $transaction->type==='expense' ? '-' : '+' }}₺{{ number_format($transaction->amount, 2) }}
                        </div>
                        <div style="font-size:13px;color:var(--muted);margin-top:2px;">
                            {{ $transaction->type==='income' ? 'Giriş' : ($transaction->type==='expense' ? 'Çıkış' : 'Transfer') }}
                        </div>
                    </div>
                </div>
                @if($transaction->status==='completed')
                    <span class="badge badge-green" style="font-size:13px;padding:6px 14px;">✓ Tamamlandı</span>
                @elseif($transaction->status==='pending')
                    <span class="badge badge-yellow" style="font-size:13px;padding:6px 14px;">⧗ Bekliyor</span>
                @else
                    <span class="badge badge-red" style="font-size:13px;padding:6px 14px;">✕ Başarısız</span>
                @endif
            </div>

            <hr class="divider">

            {{-- DETAILS --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-bottom:6px;">Referans No</div>
                    <div class="mono" style="font-size:14px;">{{ $transaction->reference_number }}</div>
                </div>
                <div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-bottom:6px;">Oluşturulma</div>
                    <div class="mono" style="font-size:14px;">{{ $transaction->created_at->format('d.m.Y H:i') }}</div>
                </div>
                <div style="grid-column:span 2;">
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-bottom:6px;">Açıklama</div>
                    <div style="font-size:14px;color:{{ $transaction->description ? 'var(--text)' : 'var(--muted)' }};">{{ $transaction->description ?? 'Açıklama girilmemiş' }}</div>
                </div>
            </div>

            @if($transaction->status === 'pending')
            <hr class="divider">
            <div style="display:flex;gap:10px;">
                <a href="{{ route('transactions.edit', $transaction) }}" class="btn-secondary" style="color:#f5c842;">Düzenle</a>
                <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Bu işlem silinsin mi?')">
                    @csrf @method('DELETE')
                    <button class="btn-danger">Sil</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>