@extends('layouts.app')
@section('title', 'İşlemler')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold gradient-text">İşlemler</h2>
            <p class="text-slate-400 mt-1">{{ $transactions->total() }} kayıt</p>
        </div>
        <a href="{{ route('transactions.create') }}" 
           class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl font-medium transition-all flex items-center gap-2">
            <i class="fas fa-plus"></i> Yeni İşlem
        </a>
    </div>

    {{-- Özet Bantı --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="glass rounded-xl p-4 text-center">
            <p class="text-slate-400 text-xs mb-1">Gelir</p>
            <p class="text-emerald-400 font-bold">{{ number_format($summary['total_income'], 0) }} ₹</p>
        </div>
        <div class="glass rounded-xl p-4 text-center">
            <p class="text-slate-400 text-xs mb-1">Gider</p>
            <p class="text-red-400 font-bold">{{ number_format($summary['total_expense'], 0) }} ₹</p>
        </div>
        <div class="glass rounded-xl p-4 text-center">
            <p class="text-slate-400 text-xs mb-1">Net</p>
            <p class="{{ $summary['net_balance'] >= 0 ? 'text-emerald-400' : 'text-red-400' }} font-bold">
                {{ number_format($summary['net_balance'], 0) }} ₹
            </p>
        </div>
    </div>

    {{-- Filtreler --}}
    <div class="glass rounded-2xl p-4">
        <form method="GET" class="flex gap-3 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Açıklamada ara..."
                   class="bg-white/10 border border-white/20 rounded-xl px-4 py-2 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-indigo-500 flex-1 min-w-48">

            <select name="type" class="bg-white/10 border border-white/20 rounded-xl px-4 py-2 text-slate-300 text-sm focus:outline-none focus:border-indigo-500">
                <option value="">Tüm Türler</option>
                <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Gelir</option>
                <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Gider</option>
            </select>

            <select name="category" class="bg-white/10 border border-white/20 rounded-xl px-4 py-2 text-slate-300 text-sm focus:outline-none focus:border-indigo-500">
                <option value="">Tüm Kategoriler</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>

            <select name="period" class="bg-white/10 border border-white/20 rounded-xl px-4 py-2 text-slate-300 text-sm focus:outline-none focus:border-indigo-500">
                <option value="">Tüm Zaman</option>
                <option value="this_month" {{ request('period') === 'this_month' ? 'selected' : '' }}>Bu Ay</option>
                <option value="last_month" {{ request('period') === 'last_month' ? 'selected' : '' }}>Geçen Ay</option>
                <option value="last_30" {{ request('period') === 'last_30' ? 'selected' : '' }}>Son 30 Gün</option>
                <option value="last_90" {{ request('period') === 'last_90' ? 'selected' : '' }}>Son 90 Gün</option>
                <option value="this_year" {{ request('period') === 'this_year' ? 'selected' : '' }}>Bu Yıl</option>
            </select>

            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-sm transition-all">
                <i class="fas fa-search"></i>
            </button>
            <a href="{{ route('transactions.index') }}" class="bg-white/10 hover:bg-white/20 text-slate-300 px-4 py-2 rounded-xl text-sm transition-all">
                Temizle
            </a>
        </form>
    </div>

    {{-- Tablo --}}
    <div class="glass rounded-2xl overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-white/10">
                    <th class="text-left px-6 py-4 text-slate-400 text-sm font-medium">Tarih</th>
                    <th class="text-left px-6 py-4 text-slate-400 text-sm font-medium">Açıklama</th>
                    <th class="text-left px-6 py-4 text-slate-400 text-sm font-medium">Kategori</th>
                    <th class="text-left px-6 py-4 text-slate-400 text-sm font-medium">Kaynak</th>
                    <th class="text-right px-6 py-4 text-slate-400 text-sm font-medium">Tutar</th>
                    <th class="text-right px-6 py-4 text-slate-400 text-sm font-medium">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($transactions as $tx)
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="px-6 py-4 text-slate-400 text-sm whitespace-nowrap">
                        {{ $tx->transaction_date->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-white text-sm">{{ $tx->description ?? '—' }}</p>
                        @if($tx->location)
                            <p class="text-slate-500 text-xs mt-0.5"><i class="fas fa-map-marker-alt mr-1"></i>{{ $tx->location }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($tx->category)
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            {{ $tx->category }}
                        </span>
                        @else
                        <span class="text-slate-600 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs text-slate-500">
                            @if($tx->source === 'csv') 📄 CSV
                            @elseif($tx->source === 'api') 🔌 API
                            @else ✏️ Manuel
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="font-bold text-sm {{ $tx->amount >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $tx->amount >= 0 ? '+' : '-' }}{{ number_format(abs($tx->amount), 2) }} ₹
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('transactions.edit', $tx) }}" 
                               class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white transition-all">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            <form action="{{ route('transactions.destroy', $tx) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Bu işlemi silmek istiyor musunuz?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg bg-white/5 hover:bg-red-500/20 text-slate-400 hover:text-red-400 transition-all">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                        <i class="fas fa-inbox text-4xl mb-3 block"></i>
                        İşlem bulunamadı. <a href="{{ route('transactions.create') }}" class="text-indigo-400 hover:underline">İlk işlemi ekle</a>
                        veya <a href="{{ route('transactions.import.form') }}" class="text-indigo-400 hover:underline">CSV yükle.</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4 border-t border-white/10">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
