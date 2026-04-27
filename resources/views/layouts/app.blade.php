<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'BankAnaliz') }} — {{ $title ?? 'Panel' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --bg: #0a0a0f;
            --surface: #111118;
            --surface2: #1a1a24;
            --border: rgba(255,255,255,0.07);
            --accent: #c9f31d;
            --accent2: #4ade80;
            --accent3: #f87171;
            --accent4: #a78bfa;
            --text: #e8e8f0;
            --muted: #6b6b80;
            --gold: #f5c842;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            top: -40%; right: -20%;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(201,243,29,0.04) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -30%; left: -10%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(74,222,128,0.03) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }
        .layout { display: flex; min-height: 100vh; position: relative; z-index: 1; }

        .sidebar {
            width: 260px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }
        .sidebar-logo { padding: 28px 24px 20px; border-bottom: 1px solid var(--border); }
        .logo-mark { display: flex; align-items: center; gap: 10px; }
        .logo-icon {
            width: 36px; height: 36px;
            background: var(--accent);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .logo-icon svg { width: 20px; height: 20px; }
        .logo-text { font-family: 'Playfair Display', serif; font-size: 18px; color: var(--text); letter-spacing: -0.3px; }
        .logo-sub  { font-size: 10px; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-top: 1px; }

        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .nav-section { margin-bottom: 24px; }
        .nav-label {
            font-size: 10px; letter-spacing: 2px; text-transform: uppercase;
            color: var(--muted); padding: 0 12px; margin-bottom: 6px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 9px 12px; border-radius: 10px;
            color: var(--muted); text-decoration: none;
            font-size: 13.5px; font-weight: 500;
            transition: all 0.18s; margin-bottom: 2px;
            position: relative;
        }
        .nav-item:hover { background: var(--surface2); color: var(--text); }
        .nav-item.active { background: rgba(201,243,29,0.08); color: var(--accent); }
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: -12px; top: 50%; transform: translateY(-50%);
            width: 3px; height: 18px;
            background: var(--accent);
            border-radius: 0 3px 3px 0;
        }
        .nav-icon { width: 17px; height: 17px; opacity: 0.65; flex-shrink: 0; }
        .nav-item.active .nav-icon,
        .nav-item:hover .nav-icon { opacity: 1; }
        .nav-badge {
            margin-left: auto;
            font-size: 9px; font-weight: 600;
            padding: 2px 6px; border-radius: 5px;
            letter-spacing: 0.04em;
        }
        .nav-badge-count { background: rgba(201,243,29,0.12); color: var(--accent); }
        .nav-badge-new   { background: transparent; color: var(--accent); border: 1px solid rgba(201,243,29,0.3); }

        .sidebar-user { padding: 14px 12px; border-top: 1px solid var(--border); }
        .user-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 10px;
            background: var(--surface2);
        }
        .user-avatar {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, var(--accent), #86efac);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            color: #0a0a0f; flex-shrink: 0;
        }
        .user-name  { font-size: 13px; font-weight: 500; color: var(--text); }
        .user-email { font-size: 11px; color: var(--muted); }
        .user-actions { margin-left: auto; display: flex; gap: 4px; }
        .user-action-btn {
            width: 28px; height: 28px; border-radius: 7px;
            background: var(--surface); border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--muted); text-decoration: none;
            transition: all 0.15s;
        }
        .user-action-btn:hover { color: var(--text); border-color: rgba(255,255,255,0.15); }
        .user-action-btn svg { width: 13px; height: 13px; }

        .main { margin-left: 260px; flex: 1; display: flex; flex-direction: column; }
        .topbar {
            height: 64px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px;
            border-bottom: 1px solid var(--border);
            background: rgba(10,10,15,0.85);
            backdrop-filter: blur(20px);
            position: sticky; top: 0; z-index: 50;
        }
        .page-title { font-family: 'Playfair Display', serif; font-size: 21px; color: var(--text); }
        .topbar-actions { display: flex; align-items: center; gap: 10px; }

        .btn-primary {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 16px;
            background: var(--accent); color: #0a0a0f;
            border-radius: 9px; font-size: 13px; font-weight: 600;
            text-decoration: none; transition: all 0.18s;
            border: none; cursor: pointer; font-family: 'DM Sans', sans-serif;
        }
        .btn-primary:hover { background: #d4f530; transform: translateY(-1px); }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        .btn-secondary {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 16px;
            background: var(--surface2); color: var(--text);
            border-radius: 9px; font-size: 13px; font-weight: 500;
            text-decoration: none; transition: all 0.18s;
            border: 1px solid var(--border); cursor: pointer;
            font-family: 'DM Sans', sans-serif;
        }
        .btn-secondary:hover { border-color: rgba(255,255,255,0.15); background: #22222e; }

        .btn-danger {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 16px;
            background: rgba(248,113,113,0.1); color: var(--accent3);
            border-radius: 9px; font-size: 13px; font-weight: 500;
            text-decoration: none; transition: all 0.18s;
            border: 1px solid rgba(248,113,113,0.2); cursor: pointer;
            font-family: 'DM Sans', sans-serif;
        }
        .btn-danger:hover { background: rgba(248,113,113,0.2); }

        .content { padding: 28px 32px; flex: 1; }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px; padding: 24px;
        }
        .card-sm { padding: 18px 20px; }

        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 9px; border-radius: 6px;
            font-size: 11px; font-weight: 500;
            font-family: 'DM Mono', monospace;
        }
        .badge-green  { background: rgba(74,222,128,0.1);  color: #4ade80; }
        .badge-yellow { background: rgba(245,200,66,0.1);  color: #f5c842; }
        .badge-red    { background: rgba(248,113,113,0.1); color: #f87171; }
        .badge-blue   { background: rgba(96,165,250,0.1);  color: #60a5fa; }
        .badge-purple { background: rgba(167,139,250,0.1); color: #a78bfa; }
        .badge-lime   { background: rgba(201,243,29,0.1);  color: #c9f31d; }

        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            text-align: left; font-size: 11px;
            letter-spacing: 1.5px; text-transform: uppercase;
            color: var(--muted); padding: 12px 16px;
            border-bottom: 1px solid var(--border); font-weight: 500;
        }
        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(255,255,255,0.02); }
        tbody td { padding: 13px 16px; font-size: 13.5px; }
        .mono { font-family: 'DM Mono', monospace; font-size: 12px; }

        .alert { padding: 11px 16px; border-radius: 10px; font-size: 13.5px; margin-bottom: 20px; }
        .alert-success { background: rgba(74,222,128,0.08); border: 1px solid rgba(74,222,128,0.2); color: #4ade80; }
        .alert-error   { background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.2); color: #f87171; }

        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block; font-size: 11px; font-weight: 500;
            color: var(--muted); letter-spacing: 0.5px;
            margin-bottom: 7px; text-transform: uppercase;
        }
        .form-control {
            width: 100%; background: var(--surface2);
            border: 1px solid var(--border); border-radius: 10px;
            padding: 10px 14px; font-size: 13.5px; color: var(--text);
            font-family: 'DM Sans', sans-serif;
            transition: border-color 0.18s; outline: none;
        }
        .form-control:focus { border-color: var(--accent); }
        .form-control option { background: var(--surface2); }
        .form-error { font-size: 12px; color: var(--accent3); margin-top: 5px; }

        .pagination { display: flex; gap: 5px; justify-content: center; padding-top: 20px; }
        .pagination a, .pagination span {
            padding: 6px 11px; border-radius: 8px; font-size: 13px;
            background: var(--surface2); color: var(--muted);
            text-decoration: none; border: 1px solid var(--border); transition: all 0.15s;
        }
        .pagination a:hover { color: var(--text); border-color: rgba(255,255,255,0.15); }
        .pagination .active { background: var(--accent); color: #0a0a0f; border-color: var(--accent); font-weight: 600; }

        .divider { border: none; border-top: 1px solid var(--border); margin: 22px 0; }

        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 16px; padding: 22px;
            position: relative; overflow: hidden;
            transition: border-color 0.2s, transform 0.2s;
        }
        .stat-card:hover { border-color: rgba(255,255,255,0.12); transform: translateY(-2px); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; }
        .stat-card.green::before  { background: var(--accent2); }
        .stat-card.lime::before   { background: var(--accent); }
        .stat-card.red::before    { background: var(--accent3); }
        .stat-card.purple::before { background: var(--accent4); }
        .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: var(--muted); font-weight: 500; }
        .stat-value { font-family: 'DM Mono', monospace; font-size: 26px; font-weight: 500; margin-top: 10px; letter-spacing: -1px; }
        .stat-value.green  { color: var(--accent2); }
        .stat-value.lime   { color: var(--accent); }
        .stat-value.red    { color: var(--accent3); }
        .stat-value.purple { color: var(--accent4); }
        .stat-icon {
            position: absolute; top: 18px; right: 18px;
            width: 38px; height: 38px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center; opacity: 0.14;
        }
        .stat-icon svg { width: 20px; height: 20px; }
        .stat-change { font-size: 12px; color: var(--muted); margin-top: 7px; }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--surface2); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--border); }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main { margin-left: 0; }
            .content { padding: 20px 16px; }
        }

        #ba-chat-btn {
            position: fixed; bottom: 24px; right: 24px;
            width: 46px; height: 46px; border-radius: 13px;
            background: var(--accent); border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            z-index: 200;
            box-shadow: 0 4px 24px rgba(201,243,29,0.2);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        #ba-chat-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(201,243,29,0.3); }
        #ba-chat-btn svg  { width: 19px; height: 19px; color: #0a0a0f; }

        #ba-chat-panel {
            position: fixed; bottom: 82px; right: 24px;
            width: 344px; max-height: 530px;
            background: var(--surface);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 16px;
            display: flex; flex-direction: column;
            z-index: 200;
            box-shadow: 0 24px 64px rgba(0,0,0,0.7);
            overflow: hidden;
            transform: translateY(12px) scale(0.96);
            opacity: 0; pointer-events: none;
            transition: all 0.22s cubic-bezier(0.4,0,0.2,1);
        }
        #ba-chat-panel.open { transform: translateY(0) scale(1); opacity: 1; pointer-events: all; }

        .chat-head {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
            flex-shrink: 0;
        }
        .chat-head-icon {
            width: 30px; height: 30px; border-radius: 8px;
            background: rgba(201,243,29,0.12);
            display: flex; align-items: center; justify-content: center;
            color: var(--accent); font-size: 13px; font-weight: 700;
            flex-shrink: 0;
        }
        .chat-head-name   { font-size: 13px; font-weight: 600; color: var(--text); }
        .chat-head-status { font-size: 10px; color: var(--muted); display: flex; align-items: center; gap: 4px; }
        .chat-status-dot  { width: 5px; height: 5px; border-radius: 50%; background: var(--accent2); }
        .chat-head-close  {
            margin-left: auto; background: none; border: none;
            cursor: pointer; color: var(--muted); font-size: 16px;
            padding: 2px 5px; transition: color 0.15s; line-height: 1;
        }
        .chat-head-close:hover { color: var(--text); }

        .chat-messages {
            flex: 1; overflow-y: auto;
            padding: 14px; display: flex;
            flex-direction: column; gap: 10px; min-height: 0;
        }
        .chat-messages::-webkit-scrollbar { width: 2px; }
        .chat-messages::-webkit-scrollbar-thumb { background: var(--surface2); }

        .msg {
            max-width: 90%; font-size: 12.5px; line-height: 1.55;
            border-radius: 11px; padding: 9px 12px;
            white-space: pre-wrap; word-break: break-word;
        }
        .msg-bot  {
            background: var(--surface2); border: 1px solid var(--border);
            color: #c8c8d8; align-self: flex-start; border-bottom-left-radius: 3px;
        }
        .msg-user {
            background: rgba(201,243,29,0.12); color: var(--text);
            align-self: flex-end; border-bottom-right-radius: 3px;
        }
        .msg-time { font-size: 9px; color: var(--muted); margin-top: 3px; }
        .msg-time-bot  { align-self: flex-start; padding-left: 4px; }
        .msg-time-user { align-self: flex-end;   padding-right: 4px; }

        .typing-indicator {
            display: flex; align-items: center; gap: 4px;
            padding: 10px 13px;
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 11px; border-bottom-left-radius: 3px;
            align-self: flex-start;
        }
        .typing-dot {
            width: 5px; height: 5px; border-radius: 50%;
            background: var(--muted);
            animation: tdot 1.2s infinite;
        }
        .typing-dot:nth-child(2) { animation-delay: 0.18s; }
        .typing-dot:nth-child(3) { animation-delay: 0.36s; }
        @keyframes tdot {
            0%,60%,100% { transform: translateY(0); background: var(--muted); }
            30%          { transform: translateY(-5px); background: var(--accent); }
        }

        .chat-chips {
            padding: 8px 12px 0;
            display: flex; flex-wrap: wrap; gap: 5px; flex-shrink: 0;
        }
        .chip {
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 6px; padding: 4px 9px;
            font-size: 10px; color: var(--muted);
            cursor: pointer; transition: all 0.15s;
            font-family: 'DM Sans', sans-serif;
        }
        .chip:hover { background: rgba(201,243,29,0.08); color: var(--accent); border-color: rgba(201,243,29,0.25); }

        .chat-input-row {
            padding: 10px 12px 13px;
            display: flex; gap: 6px; flex-shrink: 0;
            border-top: 1px solid var(--border);
            background: var(--bg);
        }
        #ba-chat-input {
            flex: 1; background: var(--surface2);
            border: 1px solid var(--border); border-radius: 9px;
            padding: 8px 11px; color: var(--text); font-size: 12.5px;
            font-family: 'DM Sans', sans-serif; outline: none;
            transition: border-color 0.15s; resize: none; line-height: 1.4; max-height: 80px;
        }
        #ba-chat-input:focus { border-color: rgba(201,243,29,0.4); }
        #ba-chat-input::placeholder { color: var(--muted); }
        #ba-chat-send {
            width: 34px; height: 34px; border-radius: 9px;
            background: var(--accent); border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; align-self: flex-end; transition: opacity 0.15s;
        }
        #ba-chat-send:hover { opacity: 0.85; }
        #ba-chat-send svg { width: 14px; height: 14px; color: #0a0a0f; }

        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="layout">

    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-mark">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#0a0a0f" stroke-width="2.5">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
                <div>
                    <div class="logo-text">BankAnaliz</div>
                    <div class="logo-sub">Finansal Panel</div>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-label">Genel</div>
                <a href="{{ route('dashboard') }}"
                   class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    Dashboard
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-label">İşlemler</div>
                <a href="{{ route('transactions.index') }}"
                   class="nav-item {{ request()->routeIs('transactions.index') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                    </svg>
                    Tüm İşlemler
                    <span class="nav-badge nav-badge-count">
                        {{ Auth::user()->transactions()->count() }}
                    </span>
                </a>
                <a href="{{ route('transactions.create') }}"
                   class="nav-item {{ request()->routeIs('transactions.create') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>
                    </svg>
                    Yeni İşlem
                </a>
                <a href="{{ route('transactions.csvImport.page') }}"
                   class="nav-item {{ request()->routeIs('transactions.csvImport.page') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <path d="M8 13h2m-2 4h6m-4-8h4"/>
                    </svg>
                    CSV Yükle
                    <span class="nav-badge nav-badge-new">Yeni</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-label">Analiz</div>
                <a href="{{ route('analytics') }}"
                   class="nav-item {{ request()->routeIs('analytics') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Analizler
                    <span class="nav-badge nav-badge-new">Yeni</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-label">Hesap</div>
                <a href="{{ route('profile.edit') }}"
                   class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                    Profil
                </a>
            </div>
        </nav>

        <div class="sidebar-user">
            <div class="user-card">
                <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
                <div>
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-email">{{ Str::limit(Auth::user()->email, 22) }}</div>
                </div>
                <div class="user-actions">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="user-action-btn" title="Çıkış">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <div class="main">
        <div class="topbar">
            <div class="page-title">{{ $header ?? 'Panel' }}</div>
            <div class="topbar-actions">
                {{ $topbarActions ?? '' }}
            </div>
        </div>
        <div class="content">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            {{ $slot }}
        </div>
    </div>

</div>

<button id="ba-chat-btn" title="Finansal Asistan" aria-label="Chatı Aç">
    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7">
        <path d="M18 10c0 4.4-3.6 8-8 8a8 8 0 01-3.9-1L2 18l1.2-3.9A8 8 0 1118 10z"/>
    </svg>
</button>

<div id="ba-chat-panel" role="dialog" aria-label="Finansal Asistan">
    <div class="chat-head">
        <div class="chat-head-icon">A</div>
        <div>
            <div class="chat-head-name">Finansal Asistan</div>
            <div class="chat-head-status">
                <div class="chat-status-dot"></div>
                Çevrimiçi · Verilerinizi analiz eder
            </div>
        </div>
        <button class="chat-head-close" id="ba-chat-close">✕</button>
    </div>

    <div class="chat-messages" id="ba-chat-messages"></div>

    <div class="chat-chips">
        <button class="chip" onclick="sendChip('bakiye')">Bakiye</button>
        <button class="chip" onclick="sendChip('bu ay özeti')">Bu ay</button>
        <button class="chip" onclick="sendChip('harcama trendi')">Trend</button>
        <button class="chip" onclick="sendChip('en yüksek işlem')">Max</button>
        <button class="chip" onclick="sendChip('yardım')">Yardım</button>
    </div>

    <div class="chat-input-row">
        <textarea id="ba-chat-input" placeholder="Bir şey sorun..." rows="1" autocomplete="off"></textarea>
        <button id="ba-chat-send" aria-label="Gönder">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M14 2L2 7.5l5 1.5L9 14l5-12z"/>
            </svg>
        </button>
    </div>
</div>

<script>
(function () {
    const panel    = document.getElementById('ba-chat-panel');
    const btn      = document.getElementById('ba-chat-btn');
    const closeBtn = document.getElementById('ba-chat-close');
    const input    = document.getElementById('ba-chat-input');
    const sendBtn  = document.getElementById('ba-chat-send');
    const messages = document.getElementById('ba-chat-messages');
    let isOpen = false;

    function togglePanel() {
        isOpen = !isOpen;
        panel.classList.toggle('open', isOpen);
        if (isOpen) {
            setTimeout(() => input.focus(), 220);
            if (messages.children.length === 0) addWelcome();
        }
    }

    btn.addEventListener('click', togglePanel);
    closeBtn.addEventListener('click', togglePanel);

    function addWelcome() {
        addMsg('bot',
            'Merhaba! Ben BankAnaliz asistanınım.\n' +
            'Verilerinizi gerçek zamanlı analiz ederim.\n\n' +
            '"bakiye", "bu ay", "trend" veya "yardım" yazabilirsiniz.'
        );
    }

    function sendMessage(text) {
        text = text.trim();
        if (!text) return;
        addMsg('user', text);
        input.value = '';
        autoResize();
        const ind = addTyping();
        fetch('{{ route("chat.ask") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: text }),
        })
        .then(r => r.json())
        .then(data => { ind.remove(); addMsg('bot', data.reply || 'Yanıt alınamadı.'); })
        .catch(() => { ind.remove(); addMsg('bot', 'Bağlantı hatası. Lütfen tekrar deneyin.'); });
    }

    function sendChip(text) {
        if (!isOpen) togglePanel();
        setTimeout(() => sendMessage(text), isOpen ? 0 : 240);
    }
    window.sendChip = sendChip;

    sendBtn.addEventListener('click', () => sendMessage(input.value));
    input.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(input.value); }
    });
    input.addEventListener('input', autoResize);

    function autoResize() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 80) + 'px';
    }

    function addMsg(role, text) {
        const wrap = document.createElement('div');
        wrap.style.cssText = 'display:flex;flex-direction:column';
        const msg = document.createElement('div');
        msg.className = role === 'bot' ? 'msg msg-bot' : 'msg msg-user';
        msg.textContent = text;
        const time = document.createElement('div');
        time.className = role === 'bot' ? 'msg-time msg-time-bot' : 'msg-time msg-time-user';
        time.textContent = new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
        wrap.appendChild(msg);
        wrap.appendChild(time);
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
        return wrap;
    }

    function addTyping() {
        const ind = document.createElement('div');
        ind.className = 'typing-indicator';
        ind.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
        messages.appendChild(ind);
        messages.scrollTop = messages.scrollHeight;
        return ind;
    }
})();
</script>

</body>
</html>