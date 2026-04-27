@extends('layouts.app')
@section('title', isset($transaction) ? 'İşlem Düzenle' : 'Yeni İşlem')

@section('content')
<div class="max-w-2xl">
    <div class="mb-8">
        <h2 class="text-3xl font-bold gradient-text">
            {{ isset($transaction) ? 'İşlem Düzenle' : 'Yeni İşlem Ekle' }}
        </h2>
        <p class="text-slate-400 mt-1">Seçenek 1: Manuel giriş</p>
    </div>

    <div class="glass rounded-2xl p-8">
        <form action="{{ isset($transaction) ? route('transactions.update', $transaction) : route('transactions.store') }}" 
              method="POST" class="space-y-6">
            @csrf
            @if(isset($transaction)) @method('PUT') @endif

            {{-- Tür Seçimi --}}
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-3">İşlem Türü</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="type" value="expense" class="sr-only peer"
                               {{ old('type', isset($transaction) && $transaction->amount < 0 ? 'expense' : 'expense') === 'expense' ? 'checked' : '' }}>
                        <div class="p-4 rounded-xl border-2 border-white/10 peer-checked:border-red-500 peer-checked:bg-red-500/10 transition-all text-center">
                            <i class="fas fa-arrow-down text-red-400 text-xl mb-2 block"></i>
                            <span class="text-slate-300 font-medium">Gider</span>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="type" value="income" class="sr-only peer"
                               {{ old('type', isset($transaction) && $transaction->amount > 0 ? 'income' : '') === 'income' ? 'checked' : '' }}>
                        <div class="p-4 rounded-xl border-2 border-white/10 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 transition-all text-center">
                            <i class="fas fa-arrow-up text-emerald-400 text-xl mb-2 block"></i>
                            <span class="text-slate-300 font-medium">Gelir</span>
                        </div>
                    </label>
                </div>
                @error('type') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Tutar --}}
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-2">Tutar (₹)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">₹</span>
                    <input type="number" name="amount" step="0.01" min="0"
                           value="{{ old('amount', isset($transaction) ? abs($transaction->amount) : '') }}"
                           class="w-full bg-white/10 border border-white/20 rounded-xl pl-10 pr-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-all"
                           placeholder="0.00" required>
                </div>
                @error('amount') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Tarih --}}
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-2">Tarih</label>
                <input type="date" name="transaction_date"
                       value="{{ old('transaction_date', isset($transaction) ? $transaction->transaction_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                       class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500 transition-all" required>
                @error('transaction_date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Açıklama --}}
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-2">Açıklama</label>
                <input type="text" name="description"
                       value="{{ old('description', $transaction->description ?? '') }}"
                       class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-all"
                       placeholder="İşlem açıklaması...">
                @error('description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Kategori --}}
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-2">Kategori</label>
                <select name="category"
                        class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-slate-300 focus:outline-none focus:border-indigo-500 transition-all">
                    <option value="">Otomatik Belirle</option>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}" {{ old('category', $transaction->category ?? '') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Lokasyon --}}
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-2">Konum (İsteğe bağlı)</label>
                <input type="text" name="location"
                       value="{{ old('location', $transaction->location ?? '') }}"
                       class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-all"
                       placeholder="Şehir veya lokasyon...">
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit"
                        class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-medium py-3 rounded-xl transition-all">
                    {{ isset($transaction) ? 'Güncelle' : 'İşlem Ekle' }}
                </button>
                <a href="{{ route('transactions.index') }}"
                   class="px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 font-medium transition-all">
                    İptal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
