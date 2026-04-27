<div style="width:220px;background:#111;padding:20px;height:100vh;overflow-y:auto;">
    <h3 style="color:white;margin-bottom:20px;font-size:18px;font-weight:bold;">{{ __('Menu') }}</h3>

    <ul style="list-style:none;padding:0;margin:0;">
        <li style="margin-bottom:12px;">
            <a href="{{ route('dashboard') }}" style="color:white;text-decoration:none;display:block;padding:8px 12px;border-radius:4px;{{ request()->routeIs('dashboard') ? 'background:#2d2d2d;' : 'hover_background:#2d2d2d;' }}">
                📊 {{ __('Dashboard') }}
            </a>
        </li>
        <li style="margin-bottom:12px;">
            <a href="{{ route('transactions.index') }}" style="color:white;text-decoration:none;display:block;padding:8px 12px;border-radius:4px;{{ request()->routeIs('transactions.*') ? 'background:#2d2d2d;' : '' }}">
                💳 {{ __('Transactions') }}
            </a>
        </li>
        <li style="margin-bottom:12px;">
            <a href="{{ route('analytics') }}" style="color:white;text-decoration:none;display:block;padding:8px 12px;border-radius:4px;{{ request()->routeIs('analytics') ? 'background:#2d2d2d;' : '' }}">
                📈 {{ __('Analytics') }}
            </a>
        </li>

        <li style="margin-top:24px;padding-top:20px;border-top:1px solid #2d2d2d;">
            <p style="color:#888;font-size:12px;margin-bottom:12px;font-weight:bold;">{{ __('CSV Import') }}</p>
            <form action="{{ route('transactions.csvImport') }}" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:8px;">
                @csrf
                <input type="file" name="csv" accept=".csv,.txt" style="padding:6px;font-size:12px;color:white;background:#2d2d2d;border:1px solid #444;border-radius:4px;" required>
                <button type="submit" style="padding:8px;background:#0066cc;color:white;border:none;border-radius:4px;font-weight:bold;cursor:pointer;font-size:12px;">
                    {{ __('Upload CSV') }}
                </button>
            </form>
            @error('csv')
                <p style="color:#ff6b6b;font-size:11px;margin-top:6px;">{{ $message }}</p>
            @enderror
        </li>
    </ul>
</div>