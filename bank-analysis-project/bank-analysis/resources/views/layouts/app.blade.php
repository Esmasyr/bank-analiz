<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BankAnaliz') — Finansal Analiz Sistemi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #0f172a; }
        .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
        .gradient-text { background: linear-gradient(135deg, #6366f1, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 20px 40px rgba(99,102,241,0.2); }
        .income { color: #10b981; }
        .expense { color: #f43f5e; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #6366f1; border-radius: 2px; }
    </style>
</head>
<body class="text-slate-100 min-h-screen">

{{-- Sidebar --}}
<div class="flex">
    <aside class="w-64 min-h-screen glass border-r border-white/10 fixed left-0 top-0 z-10">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                <div>
                    <h1 class="font-bold text-white text-lg leading-tight">BankAnaliz</h1>
                    <p class="text-xs text-slate-400">Finansal Analiz</p>
                </div>
            </div>

            <nav class="space-y-1">
                <a href="{{ route('dashboard') }}" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition-all {{ request()->routeIs('dashboard') ? 'bg-indigo-600/30 text-white border border-indigo-500/30' : '' }}">
                    <i class="fas fa-home w-5"></i> Dashboard
                </a>
                <a href="{{ route('transactions.index') }}" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition-all {{ request()->routeIs('transactions.*') ? 'bg-indigo-600/30 text-white border border-indigo-500/30' : '' }}">
                    <i class="fas fa-list w-5"></i> İşlemler
                </a>
                <a href="{{ route('transactions.create') }}" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition-all">
                    <i class="fas fa-plus-circle w-5"></i> İşlem Ekle
                </a>
                <a href="{{ route('transactions.import.form') }}" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition-all">
                    <i class="fas fa-file-csv w-5"></i> CSV Yükle
                </a>
            </nav>

            <div class="mt-8 p-4 rounded-xl bg-gradient-to-br from-indigo-600/20 to-purple-600/20 border border-indigo-500/20">
                <p class="text-xs text-slate-400 mb-2">API Simülasyonu</p>
                <form action="{{ route('transactions.api.simulate') }}" method="POST">
                    @csrf
                    <div class="flex gap-2">
                        <input type="number" name="count" value="20" min="1" max="50"
                               class="w-16 bg-white/10 border border-white/20 rounded-lg px-2 py-1 text-sm text-white">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white text-xs px-3 py-1 rounded-lg transition-all">
                            Üret
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <main class="ml-64 flex-1 p-8">
        @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/30 text-red-300">
            <ul class="space-y-1">
                @foreach($errors->all() as $e)
                    <li><i class="fas fa-exclamation-circle mr-2"></i>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
