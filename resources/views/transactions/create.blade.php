{{-- CREATE --}}
<x-app-layout>
    <x-slot name="header">Yeni İşlem</x-slot>
    <x-slot name="topbarActions">
        <a href="{{ route('transactions.index') }}" class="btn-secondary">← Geri</a>
    </x-slot>

    <div style="max-width:560px;">
        <div class="card">
            <form method="POST" action="{{ route('transactions.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">İşlem Türü</label>
                    <select name="type" class="form-control">
                        <option value="">Tür seçin...</option>
                        <option value="income" {{ old('type')==='income'?'selected':'' }}>↑ Giriş (Income)</option>
                        <option value="expense" {{ old('type')==='expense'?'selected':'' }}>↓ Çıkış (Expense)</option>
                        <option value="transfer" {{ old('type')==='transfer'?'selected':'' }}>⇄ Transfer</option>
                    </select>
                    @error('type')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Tutar (₺)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" placeholder="0.00" class="form-control">
                    @error('amount')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Referans Numarası</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number', 'REF-'.strtoupper(uniqid())) }}" class="form-control" style="font-family:'DM Mono',monospace;">
                    @error('reference_number')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Açıklama <span style="color:var(--muted);font-size:10px;">(opsiyonel)</span></label>
                    <textarea name="description" rows="3" class="form-control" placeholder="İşlem açıklaması...">{{ old('description') }}</textarea>
                    @error('description')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex;gap:10px;padding-top:8px;">
                    <button type="submit" class="btn-primary">İşlem Oluştur</button>
                    <a href="{{ route('transactions.index') }}" class="btn-secondary">İptal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>