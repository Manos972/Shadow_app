<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
use App\Models\Account;
use App\Models\Category;
use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Watchlist;
use App\Models\StockAlert;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Demo Utilisateur',
            'email' => 'demo@shadow.app',
            'password' => Hash::make('password'),
            'currency' => 'CAD',
            'timezone' => 'America/Toronto',
        ]);

        // Créer l'espace personnel
        $team = Team::create([
            'name' => 'Mon espace financier',
            'owner_id' => $user->id,
            'currency' => 'CAD',
            'locale' => 'fr_CA',
            'timezone' => 'America/Toronto',
        ]);
        $team->users()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);
        $user->update(['current_team_id' => $team->id]);

        $this->call([CategorySeeder::class]);

        // Comptes canadiens
        Account::create(['user_id' => $user->id, 'team_id' => $team->id, 'name' => 'RBC - Compte chèques', 'type' => 'checking', 'balance' => 5820.45, 'initial_balance' => 5820.45, 'currency' => 'CAD', 'color' => '#3b82f6', 'icon' => 'bank']);
        Account::create(['user_id' => $user->id, 'team_id' => $team->id, 'name' => 'CÉLI / TFSA', 'type' => 'savings', 'balance' => 28500.00, 'initial_balance' => 28500.00, 'currency' => 'CAD', 'color' => '#10b981', 'icon' => 'piggy-bank']);
        Account::create(['user_id' => $user->id, 'team_id' => $team->id, 'name' => 'REER / RRSP', 'type' => 'savings', 'balance' => 42000.00, 'initial_balance' => 42000.00, 'currency' => 'CAD', 'color' => '#8b5cf6', 'icon' => 'piggy-bank']);
        Account::create(['user_id' => $user->id, 'team_id' => $team->id, 'name' => 'Espèces', 'type' => 'cash', 'balance' => 450.00, 'initial_balance' => 450.00, 'currency' => 'CAD', 'color' => '#f59e0b', 'icon' => 'cash']);

        // Portefeuille
        $portfolio = Portfolio::create(['user_id' => $user->id, 'team_id' => $team->id, 'name' => 'CÉLI - Croissance', 'description' => 'Actions canadiennes & ETF', 'currency' => 'CAD', 'color' => '#8b5cf6']);

        // Actions canadiennes + US dans le portefeuille
        $positions = [
            ['symbol' => 'SHOP.TO', 'name' => 'Shopify Inc.', 'exchange' => 'TSX', 'quantity' => 15, 'avg_buy_price' => 88.50],
            ['symbol' => 'RY.TO', 'name' => 'Banque Royale du Canada', 'exchange' => 'TSX', 'quantity' => 25, 'avg_buy_price' => 132.00],
            ['symbol' => 'TD.TO', 'name' => 'Banque TD', 'exchange' => 'TSX', 'quantity' => 30, 'avg_buy_price' => 78.50],
            ['symbol' => 'CNR.TO', 'name' => 'Chemin de fer Canadien National', 'exchange' => 'TSX', 'quantity' => 10, 'avg_buy_price' => 168.00],
            ['symbol' => 'XEQT.TO', 'name' => 'iShares Core Equity ETF', 'exchange' => 'TSX', 'quantity' => 100, 'avg_buy_price' => 29.50],
            ['symbol' => 'VFV.TO', 'name' => 'Vanguard S&P 500 ETF (CAD)', 'exchange' => 'TSX', 'quantity' => 50, 'avg_buy_price' => 112.00],
            ['symbol' => 'AAPL', 'name' => 'Apple Inc.', 'exchange' => 'NASDAQ', 'quantity' => 8, 'avg_buy_price' => 165.00],
        ];

        foreach ($positions as $pos) {
            Position::create(array_merge($pos, ['portfolio_id' => $portfolio->id, 'user_id' => $user->id, 'is_open' => true]));
        }

        // Watchlist canadienne
        $watchSymbols = [
            ['symbol' => 'NVDA', 'name' => 'NVIDIA Corporation', 'exchange' => 'NASDAQ', 'target_price' => 800.00],
            ['symbol' => 'ATD.TO', 'name' => 'Couche-Tard', 'exchange' => 'TSX', 'target_price' => 75.00],
            ['symbol' => 'BNS.TO', 'name' => 'Banque Scotia', 'exchange' => 'TSX', 'target_price' => 58.00],
            ['symbol' => 'ZSP.TO', 'name' => 'BMO S&P 500 ETF', 'exchange' => 'TSX', 'target_price' => 85.00],
        ];
        foreach ($watchSymbols as $w) {
            Watchlist::create(array_merge($w, ['user_id' => $user->id]));
        }

        // Alerte démo
        StockAlert::create(['user_id' => $user->id, 'symbol' => 'SHOP.TO', 'name' => 'Shopify', 'type' => 'rsi_below', 'threshold' => 30, 'notify_email' => true, 'is_active' => true]);
    }
}
