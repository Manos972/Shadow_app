<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Shadow Finance') }} · @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body x-data class="overflow-x-hidden">

<!-- ── Toast Container ─────────────────────────────────────── -->
<div class="fixed bottom-5 right-5 z-[100] flex flex-col gap-2" x-data>
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div class="toast" :class="`toast-${toast.type}`" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <span x-text="toast.icon" class="text-base"></span>
            <span x-text="toast.msg" class="flex-1"></span>
            <button @click="$store.toasts.remove(toast.id)" class="opacity-50 hover:opacity-100 ml-2">✕</button>
        </div>
    </template>
</div>

<!-- ── Command Palette ─────────────────────────────────────── -->
<div x-data x-show="$store.commandPalette.open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[200] flex items-start justify-center pt-24 px-4" @click.self="$store.commandPalette.open = false" style="display:none; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);">
    <div class="command-palette w-full max-w-lg">
        <div class="p-3 border-b border-white/10 flex items-center gap-3">
            <span class="text-slate-400 text-lg">⌘</span>
            <input type="text" x-model="$store.commandPalette.query" class="flex-1 bg-transparent text-white placeholder:text-slate-500 focus:outline-none text-sm" placeholder="Rechercher une page ou une action..." autofocus x-init="$watch('$store.commandPalette.open', v => v && $nextTick(() => $el.focus()))">
            <kbd class="text-xs text-slate-500 bg-white/5 px-2 py-0.5 rounded">ESC</kbd>
        </div>
        <div class="py-2 max-h-80 overflow-y-auto">
            <template x-for="(cmd, index) in $store.commandPalette.filtered" :key="cmd.url">
                <a :href="cmd.url" @click="$store.commandPalette.open = false" class="command-item" :class="{ 'selected': index === $store.commandPalette.selected }" @mouseenter="$store.commandPalette.selected = index">
                    <span x-text="cmd.icon" class="text-base w-5 text-center"></span>
                    <span x-text="cmd.label" class="flex-1"></span>
                    <template x-if="cmd.shortcut">
                        <kbd class="text-xs text-slate-600 bg-white/5 px-1.5 py-0.5 rounded font-mono" x-text="'⌘' + cmd.shortcut"></kbd>
                    </template>
                </a>
            </template>
            <div x-show="$store.commandPalette.filtered.length === 0" class="px-4 py-8 text-center text-slate-500 text-sm">Aucun résultat</div>
        </div>
        <div class="px-4 py-2 border-t border-white/10 flex items-center gap-4 text-xs text-slate-600">
            <span>↑↓ naviguer</span><span>↵ ouvrir</span><span>ESC fermer</span>
        </div>
    </div>
</div>

<!-- ── Sidebar ─────────────────────────────────────────────── -->
<aside class="sidebar hidden lg:flex w-60 bg-black/40 border-r border-white/[0.06]" style="backdrop-filter: blur(20px);">
    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 h-16 border-b border-white/[0.06] flex-shrink-0">
        <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-sm text-white" style="background: linear-gradient(135deg, #3b82f6, #8b5cf6)">S</div>
        <div>
            <div class="font-bold text-white text-sm leading-none">Shadow</div>
            <div class="text-[10px] text-slate-500 mt-0.5">Finance · CAD</div>
        </div>
        <div class="ml-auto">
            <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 live-indicator"></div>
        </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">
        <div class="sidebar-label">Général</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="text-base">📊</span><span>Dashboard</span>
        </a>

        <div class="sidebar-label">Budget</div>
        <a href="{{ route('budget.index') }}" class="nav-link {{ request()->routeIs('budget.index') ? 'active' : '' }}"><span class="text-base">💰</span><span>Vue d'ensemble</span></a>
        <a href="{{ route('budget.transactions') }}" class="nav-link {{ request()->routeIs('budget.transactions') ? 'active' : '' }}"><span class="text-base">💸</span><span>Transactions</span></a>
        <a href="{{ route('budget.accounts') }}" class="nav-link {{ request()->routeIs('budget.accounts') ? 'active' : '' }}"><span class="text-base">🏦</span><span>Comptes</span></a>
        <a href="{{ route('budget.categories') }}" class="nav-link {{ request()->routeIs('budget.categories') ? 'active' : '' }}"><span class="text-base">🏷️</span><span>Catégories</span></a>
        <a href="{{ route('budget.reports') }}" class="nav-link {{ request()->routeIs('budget.reports') ? 'active' : '' }}"><span class="text-base">📈</span><span>Rapports</span></a>
        <a href="/import" class="nav-link {{ request()->is('import*') ? 'active' : '' }}"><span class="text-base">📂</span><span>Importer</span></a>

        <div class="sidebar-label">Bourse</div>
        <a href="{{ route('portfolio.index') }}" class="nav-link {{ request()->routeIs('portfolio.index') ? 'active' : '' }}"><span class="text-base">📊</span><span>Portefeuille</span></a>
        <a href="{{ route('portfolio.positions') }}" class="nav-link {{ request()->routeIs('portfolio.positions') ? 'active' : '' }}"><span class="text-base">📉</span><span>Positions</span></a>
        <a href="{{ route('portfolio.trades') }}" class="nav-link {{ request()->routeIs('portfolio.trades') ? 'active' : '' }}"><span class="text-base">🔄</span><span>Ordres</span></a>
        <a href="{{ route('portfolio.watchlist') }}" class="nav-link {{ request()->routeIs('portfolio.watchlist') ? 'active' : '' }}"><span class="text-base">👁️</span><span>Watchlist</span></a>
        <a href="{{ route('portfolio.alerts') }}" class="nav-link {{ request()->routeIs('portfolio.alerts') ? 'active' : '' }}">
            <span class="text-base">🔔</span><span>Alertes</span>
            @php $alertCount = auth()->user()->stockAlerts()->where('is_active',true)->whereNull('triggered_at')->count() @endphp
            @if($alertCount > 0)
            <span class="ml-auto text-[10px] bg-blue-500/30 text-blue-300 border border-blue-500/30 rounded-full px-1.5 py-0.5 font-bold">{{ $alertCount }}</span>
            @endif
        </a>
        <a href="{{ route('portfolio.analysis') }}" class="nav-link {{ request()->routeIs('portfolio.analysis') ? 'active' : '' }}"><span class="text-base">🔬</span><span>Analyse tech.</span></a>
    </nav>

    <!-- User + team -->
    <div class="px-3 py-3 border-t border-white/[0.06] flex-shrink-0">
        @if(isset($currentTeam))
        <div class="mb-2 px-2 py-1.5 bg-white/[0.03] rounded-lg">
            <div class="text-[10px] text-slate-600 uppercase tracking-widest mb-0.5">Espace</div>
            <div class="text-xs text-slate-300 font-medium truncate">{{ $currentTeam->name }}</div>
        </div>
        @endif
        <div class="flex items-center gap-2 px-2 py-1.5 group">
            <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center text-white text-xs font-bold" style="background: linear-gradient(135deg, #3b82f6, #8b5cf6)">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-medium text-slate-300 truncate">{{ auth()->user()->name }}</div>
            </div>
            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <a href="/team/settings" class="text-slate-500 hover:text-blue-400 transition-colors text-sm">⚙️</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button class="text-slate-500 hover:text-red-400 transition-colors text-sm">⏻</button>
                </form>
            </div>
        </div>
    </div>
</aside>

<!-- ── Mobile header ───────────────────────────────────────── -->
<div class="lg:hidden fixed top-0 inset-x-0 z-20 h-14 flex items-center px-4 justify-between border-b border-white/[0.06]" style="background: rgba(2,8,23,0.9); backdrop-filter: blur(20px);">
    <div class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-xs text-white" style="background: linear-gradient(135deg, #3b82f6, #8b5cf6)">S</div>
        <span class="font-bold text-white text-sm">Shadow Finance</span>
    </div>
    <div class="flex items-center gap-2" x-data="{ open: false }">
        <button @click="$store.commandPalette.toggle()" class="text-slate-400 hover:text-white p-2">⌘</button>
        <button @click="open = !open" class="text-slate-400 hover:text-white p-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div x-show="open" @click.away="open = false" class="absolute right-4 top-14 w-56 py-2 command-palette z-50">
            @foreach(['dashboard' => ['📊', 'Dashboard'], 'budget.index' => ['💰', 'Budget'], 'budget.transactions' => ['💸', 'Transactions'], 'portfolio.index' => ['📊', 'Portefeuille'], 'portfolio.positions' => ['📉', 'Positions'], 'portfolio.alerts' => ['🔔', 'Alertes']] as $route => [$icon, $label])
            <a href="{{ route($route) }}" class="command-item">{{ $icon }} {{ $label }}</a>
            @endforeach
            <form method="POST" action="{{ route('logout') }}" class="px-4 py-2">@csrf<button class="text-red-400 text-sm w-full text-left">⏻ Déconnexion</button></form>
        </div>
    </div>
</div>

<!-- ── Main content ────────────────────────────────────────── -->
<div class="lg:pl-60 min-h-screen flex flex-col">
    <!-- Page header -->
    <header class="hidden lg:flex items-center justify-between h-16 px-8 border-b border-white/[0.05]" style="background: rgba(2,8,23,0.6); backdrop-filter: blur(12px);">
        <div>
            <h1 class="text-base font-bold text-white">@yield('header', 'Dashboard')</h1>
            @hasSection('subheader')
            <p class="text-xs text-slate-500 mt-0.5">@yield('subheader')</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            @yield('actions')
            <button @click="$store.commandPalette.toggle()" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-slate-500 hover:text-slate-300 transition-colors" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08)">
                <span>⌘K</span>
                <span>Recherche rapide</span>
            </button>
        </div>
    </header>

    <!-- Flash messages → auto-converted to toasts -->
    @if(session('success'))
    <script>document.addEventListener('alpine:init', () => setTimeout(() => window.toast?.success('{{ session('success') }}'), 100));<\/script>
    @endif
    @if(session('error'))
    <script>document.addEventListener('alpine:init', () => setTimeout(() => window.toast?.error('{{ session('error') }}'), 100));<\/script>
    @endif

    <!-- Content -->
    <main class="flex-1 p-6 mt-14 lg:mt-0 animate-fade-up">
        @yield('content')
    </main>

    <footer class="px-8 py-3 border-t border-white/[0.04] flex items-center justify-between text-xs text-slate-600">
        <span>Shadow Finance · Laravel 12</span>
        <span>{{ now()->format('d M Y · H:i') }}</span>
    </footer>
</div>

@livewireScripts
</body>
</html>
