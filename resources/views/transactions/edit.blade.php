<x-app-layout>
    <x-slot name="header">İşlemi Düzenle</x-slot>
    <x-slot name="topbarActions">
        <a href="{{ route('transactions.show', $transaction) }}" class="btn-secondary">← Geri</a>
    </x-slot>

    <div style="max-width:560px;">
        <div class="card">
            <form method="POST" action="{{ route('transactions.update', $transaction) }}">
                @csrf @method('PATCH')

                <div class="form-group">
                    <label class="form-label">İşlem Türü</label>
                    <select name="type" class="form-control">
                        <option value="income" {{ $transaction->type==='income'?'selected':'' }}>↑ Giriş</option>
                        <option value="expense" {{ $transaction->type==='expense'?'selected':'' }}>↓ Çıkış</option>
                        <option value="transfer" {{ $transaction->type==='transfer'?'selected':'' }}>⇄ Transfer</option>
                    </select>
                    @error('type')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Tutar (₺)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount', $transaction->amount) }}" class="form-control">
                    @error('amount')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-control">
                        <option value="pending" {{ $transaction->status==='pending'?'selected':'' }}>⧗ Bekliyor</option>
                        <option value="completed" {{ $transaction->status==='completed'?'selected':'' }}>✓ Tamamlandı</option>
                        <option value="failed" {{ $transaction->status==='failed'?'selected':'' }}>✕ Başarısız</option>
                    </select>
                    @error('status')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Açıklama <span style="color:var(--muted);font-size:10px;">(opsiyonel)</span></label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description', $transaction->description) }}</textarea>
                    @error('description')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex;gap:10px;padding-top:8px;">
                    <button type="submit" class="btn-primary">Değişiklikleri Kaydet</button>
                    <a href="{{ route('transactions.show', $transaction) }}" class="btn-secondary">İptal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>