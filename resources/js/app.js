import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

// ── Chart.js dark theme defaults ──────────────────────────────
Chart.defaults.color = '#64748b';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.font.size = 11;
Chart.defaults.plugins.legend.position = 'bottom';
Chart.defaults.plugins.legend.labels.padding = 16;
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.pointStyleWidth = 8;
Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(8,15,35,0.95)';
Chart.defaults.plugins.tooltip.borderColor = 'rgba(255,255,255,0.1)';
Chart.defaults.plugins.tooltip.borderWidth = 1;
Chart.defaults.plugins.tooltip.titleColor = '#f1f5f9';
Chart.defaults.plugins.tooltip.bodyColor = '#94a3b8';
Chart.defaults.plugins.tooltip.padding = 12;
Chart.defaults.plugins.tooltip.cornerRadius = 10;
Chart.defaults.plugins.tooltip.displayColors = true;
Chart.defaults.animation.duration = 600;
Chart.defaults.animation.easing = 'easeOutQuart';

// ── Alpine.js Stores ──────────────────────────────────────────
Alpine.store('toasts', {
    items: [],
    add(toast) {
        const id = Date.now();
        this.items.push({ id, ...toast });
        setTimeout(() => this.remove(id), 4000);
    },
    remove(id) { this.items = this.items.filter(t => t.id !== id); }
});

Alpine.store('commandPalette', {
    open: false,
    query: '',
    selected: 0,
    commands: [
        { icon: '📊', label: 'Dashboard', url: '/dashboard', shortcut: 'D' },
        { icon: '💰', label: 'Vue budget', url: '/budget' },
        { icon: '💸', label: 'Transactions', url: '/budget/transactions', shortcut: 'T' },
        { icon: '🏦', label: 'Mes comptes', url: '/budget/accounts' },
        { icon: '🏷️', label: 'Catégories', url: '/budget/categories' },
        { icon: '📈', label: 'Rapports', url: '/budget/reports' },
        { icon: '📊', label: 'Portefeuille', url: '/portfolio', shortcut: 'P' },
        { icon: '📉', label: 'Positions', url: '/portfolio/positions' },
        { icon: '🔄', label: 'Ordres', url: '/portfolio/trades' },
        { icon: '👁️', label: 'Watchlist', url: '/portfolio/watchlist' },
        { icon: '🔔', label: 'Alertes', url: '/portfolio/alerts' },
        { icon: '📂', label: 'Importer un relevé', url: '/import' },
        { icon: '👥', label: 'Mon espace', url: '/team/settings' },
    ],
    get filtered() {
        if (!this.query) return this.commands;
        const q = this.query.toLowerCase();
        return this.commands.filter(c => c.label.toLowerCase().includes(q));
    },
    toggle() { this.open = !this.open; this.query = ''; this.selected = 0; },
    navigate(dir) {
        this.selected = Math.max(0, Math.min(this.filtered.length - 1, this.selected + dir));
    },
    go() {
        const cmd = this.filtered[this.selected];
        if (cmd) window.location.href = cmd.url;
        this.open = false;
    }
});

// ── Keyboard Shortcuts ────────────────────────────────────────
document.addEventListener('keydown', (e) => {
    // Cmd/Ctrl + K = command palette
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        Alpine.store('commandPalette').toggle();
    }
    // Escape
    if (e.key === 'Escape') {
        Alpine.store('commandPalette').open = false;
    }
    // Command palette navigation
    if (Alpine.store('commandPalette').open) {
        if (e.key === 'ArrowDown') { e.preventDefault(); Alpine.store('commandPalette').navigate(1); }
        if (e.key === 'ArrowUp') { e.preventDefault(); Alpine.store('commandPalette').navigate(-1); }
        if (e.key === 'Enter') { e.preventDefault(); Alpine.store('commandPalette').go(); }
    }
});

// ── Toast utility ─────────────────────────────────────────────
window.toast = {
    success: (msg) => Alpine.store('toasts').add({ type: 'success', icon: '✅', msg }),
    error: (msg) => Alpine.store('toasts').add({ type: 'error', icon: '❌', msg }),
    info: (msg) => Alpine.store('toasts').add({ type: 'info', icon: 'ℹ️', msg }),
    warning: (msg) => Alpine.store('toasts').add({ type: 'warning', icon: '⚠️', msg }),
};

// ── Animated Number Counter ───────────────────────────────────
Alpine.directive('count', (el, { expression }, { evaluate, effect }) => {
    effect(() => {
        const target = parseFloat(evaluate(expression)) || 0;
        const decimals = (target.toString().split('.')[1] || '').length;
        const duration = 800;
        let start = null;
        const startVal = 0;
        const step = (ts) => {
            if (!start) start = ts;
            const progress = Math.min((ts - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = startVal + (target - startVal) * eased;
            el.textContent = new Intl.NumberFormat('fr-CA', {
                minimumFractionDigits: decimals > 0 ? 2 : 0,
                maximumFractionDigits: decimals > 0 ? 2 : 0
            }).format(current);
            if (progress < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    });
});

// ── Sparkline Generator ───────────────────────────────────────
window.renderSparkline = (canvasId, data, color = '#3b82f6') => {
    const canvas = document.getElementById(canvasId);
    if (!canvas || !data?.length) return;
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
    gradient.addColorStop(0, color + '33');
    gradient.addColorStop(1, 'transparent');

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.map((_, i) => i),
            datasets: [{ data, borderColor: color, borderWidth: 2, fill: true, backgroundColor: gradient, tension: 0.4, pointRadius: 0 }]
        },
        options: {
            responsive: false, animation: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } }
        }
    });
};

// ── Mouse tracking for card glow ──────────────────────────────
document.addEventListener('mousemove', (e) => {
    document.querySelectorAll('.stat-card').forEach(card => {
        const rect = card.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        card.style.setProperty('--mouse-x', x + '%');
        card.style.setProperty('--mouse-y', y + '%');
    });
});

// ── Livewire flash → toast ────────────────────────────────────
document.addEventListener('livewire:init', () => {
    Livewire.on('toast', ({ type, message }) => window.toast[type]?.(message));
});

Alpine.start();
