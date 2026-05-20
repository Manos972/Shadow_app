<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Shadow Budget') }} - @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-dark-950 min-h-screen flex">

    <!-- Sidebar -->
    <aside class="hidden lg:flex flex-col w-64 min-h-screen bg-dark-900 border-r border-dark-700 fixed inset-y-0 left-0 z-30">
        <!-- Logo -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-dark-700">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center text-white font-bold text-sm">S</div>
            <span class="font-bold text-white text-lg">Shadow</span>
            <span class="text-slate-500 text-sm">Finance</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 overflow-y-auto">
            <div class="sidebar-section">
                <div class="sidebar-label">Général</div>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span>📊</span> Dashboard
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-label">Budget</div>
                <a href="{{ route('budget.index') }}" class="nav-link {{ request()->routeIs('budget.index') ? 'active' : '' }}">
                    <span>💰</span> Vue d'ensemble
                </a>
                <a href="{{ route('budget.transactions') }}" class="nav-link {{ request()->routeIs('budget.transactions') ? 'active' : '' }}">
                    <span>💸</span> Transactions
                </a>
                <a href="{{ route('budget.accounts') }}" class="nav-link {{ request()->routeIs('budget.accounts') ? 'active' : '' }}">
                    <span>🏦</span> Comptes
                </a>
                <a href="{{ route('budget.categories') }}" class="nav-link {{ request()->routeIs('budget.categories') ? 'active' : '' }}">
                    <span>🏷️</span> Catégories
                </a>
                <a href="{{ route('budget.reports') }}" class="nav-link {{ request()->routeIs('budget.reports') ? 'active' : '' }}">
                    <span>📈</span> Rapports
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-label">Bourse</div>
                <a href="{{ route('portfolio.index') }}" class="nav-link {{ request()->routeIs('portfolio.index') ? 'active' : '' }}">
                    <span>📊</span> Portefeuille
                </a>
                <a href="{{ route('portfolio.positions') }}" class="nav-link {{ request()->routeIs('portfolio.positions') ? 'active' : '' }}">
                    <span>📉</span> Positions
                </a>
                <a href="{{ route('portfolio.trades') }}" class="nav-link {{ request()->routeIs('portfolio.trades') ? 'active' : '' }}">
                    <span>🔄</span> Historique ordres
                </a>
                <a href="{{ route('portfolio.watchlist') }}" class="nav-link {{ request()->routeIs('portfolio.watchlist') ? 'active' : '' }}">
                    <span>👁️</span> Watchlist
                </a>
                <a href="{{ route('portfolio.alerts') }}" class="nav-link {{ request()->routeIs('portfolio.alerts') ? 'active' : '' }}">
                    <span>🔔</span> Alertes
                    @php $alertCount = auth()->user()->stockAlerts()->where('is_active', true)->whereNull('triggered_at')->count() @endphp
                    @if($alertCount > 0)
                        <span class="ml-auto text-xs bg-blue-600 text-white rounded-full px-1.5 py-0.5">{{ $alertCount }}</span>
                    @endif
                </a>
            </div>
        </nav>

        <!-- User info -->
        <div class="px-3 py-4 border-t border-dark-700">
            <div class="flex items-center gap-3 px-3 py-2">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-violet-500 flex items-center justify-center text-white text-sm font-bold">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-white transition-colors text-lg">⏻</button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Mobile header -->
    <div class="lg:hidden fixed top-0 inset-x-0 z-20 bg-dark-900 border-b border-dark-700 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center text-white font-bold text-xs">S</div>
            <span class="font-bold text-white">Shadow Finance</span>
        </div>
        <div x-data="{ open: false }">
            <button @click="open = !open" class="text-slate-400 hover:text-white p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div x-show="open" @click.away="open = false" class="absolute right-0 top-full mt-1 w-56 bg-dark-800 border border-dark-700 rounded-2xl shadow-2xl p-2">
                <a href="{{ route('dashboard') }}" class="nav-link">📊 Dashboard</a>
                <a href="{{ route('budget.index') }}" class="nav-link">💰 Budget</a>
                <a href="{{ route('budget.transactions') }}" class="nav-link">💸 Transactions</a>
                <a href="{{ route('portfolio.index') }}" class="nav-link">📊 Portefeuille</a>
                <a href="{{ route('portfolio.positions') }}" class="nav-link">📉 Positions</a>
                <a href="{{ route('portfolio.watchlist') }}" class="nav-link">👁️ Watchlist</a>
                <a href="{{ route('portfolio.alerts') }}" class="nav-link">🔔 Alertes</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2 pt-2 border-t border-dark-700">
                    @csrf
                    <button class="nav-link text-red-400 w-full">⏻ Déconnexion</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="flex-1 lg:ml-64 min-h-screen flex flex-col">
        <!-- Page header -->
        <header class="bg-dark-900/50 backdrop-blur border-b border-dark-700 px-6 py-4 mt-14 lg:mt-0">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-bold text-white">@yield('header', 'Dashboard')</h1>
                    @hasSection('subheader')
                        <p class="text-sm text-slate-400 mt-0.5">@yield('subheader')</p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    @yield('actions')
                    <div class="text-xs text-slate-500">{{ now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        @if(session('success'))
            <div data-flash class="mx-6 mt-4 rounded-xl bg-emerald-900/50 border border-emerald-500/50 p-3 text-sm text-emerald-300">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div data-flash class="mx-6 mt-4 rounded-xl bg-red-900/50 border border-red-500/50 p-3 text-sm text-red-300">{{ session('error') }}</div>
        @endif

        <!-- Page content -->
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

    @livewireScripts
</body>
</html>
