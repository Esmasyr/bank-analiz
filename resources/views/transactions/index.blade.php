<x-app-layout>
    <x-slot name="header">İşlemler</x-slot>
    <x-slot name="topbarActions">
        <a href="{{ route('transactions.create') }}" class="btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Yeni İşlem
        </a>
    </x-slot>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    {{-- FİLTRE --}}
    <div class="card" style="margin-bottom:20px;">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
            <div>
                <div class="form-label">Tür</div>
                <select name="type" class="form-control" style="width:150px;">
                    <option value="">Tümü</option>
                    <option value="income" {{ request('type')==='income'?'selected':'' }}>Giriş</option>
                    <option value="expense" {{ request('type')==='expense'?'selected':'' }}>Çıkış</option>
                    <option value="transfer" {{ request('type')==='transfer'?'selected':'' }}>Transfer</option>
                </select>
            </div>
            <div>
                <div class="form-label">Durum</div>
                <select name="status" class="form-control" style="width:150px;">
                    <option value="">Tümü</option>
                    <option value="pending" {{ request('status')==='pending'?'selected':'' }}>Bekleyen</option>
                    <option value="completed" {{ request('status')==='completed'?'selected':'' }}>Tamamlanan</option>
                    <option value="failed" {{ request('status')==='failed'?'selected':'' }}>Başarısız</option>
                </select>
            </div>
            <div>
                <div class="form-label">Başlangıç</div>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" style="width:160px;">
            </div>
            <div>
                <div class="form-label">Bitiş</div>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" style="width:160px;">
            </div>
            <button type="submit" class="btn-primary">Filtrele</button>
            <a href="{{ route('transactions.index') }}" class="btn-secondary">Temizle</a>
        </form>
    </div>

    {{-- TABLO --}}
    <div class="card" style="padding:0;overflow:hidden;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Referans</th>
                        <th>Tür</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th>Açıklama</th>
                        <th>Tarih</th>
                        <th style="text-align:right;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    <tr>
                        <td><span class="mono" style="color:var(--muted);">{{ $t->reference_number }}</span></td>
                        <td>
                            @if($t->type === 'income')
                                <span class="badge badge-green">↑ Giriş</span>
                            @elseif($t->type === 'expense')
                                <span class="badge badge-red">↓ Çıkış</span>
                            @else
                                <span class="badge badge-purple">⇄ Transfer</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-family:'DM Mono',monospace;font-weight:500;
                                color:{{ $t->type==='income' ? '#4ade80' : ($t->type==='expense' ? '#f87171' : '#a78bfa') }};">
                                {{ $t->type==='expense' ? '-' : '+' }}₺{{ number_format($t->amount, 2) }}
                            </span>
                        </td>
                        <td>
                            @if($t->status === 'completed')
                                <span class="badge badge-green">Tamamlandı</span>
                            @elseif($t->status === 'pending')
                                <span class="badge badge-yellow">Bekliyor</span>
                            @else
                                <span class="badge badge-red">Başarısız</span>
                            @endif
                        </td>
                        <td style="color:var(--muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $t->description ?? '—' }}
                        </td>
                        <td><span class="mono" style="color:var(--muted);">{{ $t->created_at->format('d.m.Y H:i') }}</span></td>
                        <td>
                            <div style="display:flex;gap:8px;justify-content:flex-end;">
                                <a href="{{ route('transactions.show', $t) }}" class="btn-secondary" style="padding:6px 12px;font-size:12px;">Detay</a>
                                @if($t->status === 'pending')
                                    <a href="{{ route('transactions.edit', $t) }}" class="btn-secondary" style="padding:6px 12px;font-size:12px;color:#f5c842;">Düzenle</a>
                                    <form method="POST" action="{{ route('transactions.destroy', $t) }}" onsubmit="return confirm('Bu işlem silinsin mi?')">
                                        @csrf @method('DELETE')
                                        <button class="btn-danger" style="padding:6px 12px;font-size:12px;">Sil</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;color:var(--muted);padding:60px;">
                            <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 12px;display:block;opacity:0.3;"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Henüz işlem bulunamadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div style="padding:16px 20px;border-top:1px solid var(--border);">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</x-app-layout>