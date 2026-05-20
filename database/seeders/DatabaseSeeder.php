<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Watchlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@shadow.app',
            'password' => Hash::make('password'),
            'currency' => 'EUR',
            'timezone' => 'Europe/Paris',
        ]);

        $this->call([
            CategorySeeder::class,
        ]);

        // Comptes
        $checking = Account::create([
            'user_id' => $user->id,
            'name' => 'Compte Courant',
            'type' => 'checking',
            'balance' => 4250.80,
            'initial_balance' => 4250.80,
            'currency' => 'EUR',
            'color' => '#3b82f6',
            'icon' => 'bank',
        ]);

        Account::create([
            'user_id' => $user->id,
            'name' => 'Livret A',
            'type' => 'savings',
            'balance' => 12500.00,
            'initial_balance' => 12500.00,
            'currency' => 'EUR',
            'color' => '#10b981',
            'icon' => 'piggy-bank',
        ]);

        Account::create([
            'user_id' => $user->id,
            'name' => 'Espèces',
            'type' => 'cash',
            'balance' => 350.00,
            'initial_balance' => 350.00,
            'currency' => 'EUR',
            'color' => '#f59e0b',
            'icon' => 'cash',
        ]);

        // Portfolio
        $portfolio = Portfolio::create([
            'user_id' => $user->id,
            'name' => 'Portefeuille Principal',
            'description' => 'Actions tech & ETF',
            'currency' => 'EUR',
            'color' => '#8b5cf6',
        ]);

        // Positions de démo
        $positions = [
            ['symbol' => 'AAPL', 'name' => 'Apple Inc.', 'exchange' => 'NASDAQ', 'quantity' => 10, 'avg_buy_price' => 165.50],
            ['symbol' => 'MSFT', 'name' => 'Microsoft Corp.', 'exchange' => 'NASDAQ', 'quantity' => 5, 'avg_buy_price' => 380.00],
            ['symbol' => 'NVDA', 'name' => 'NVIDIA Corp.', 'exchange' => 'NASDAQ', 'quantity' => 3, 'avg_buy_price' => 480.00],
            ['symbol' => 'MC.PA', 'name' => 'LVMH', 'exchange' => 'EPA', 'quantity' => 2, 'avg_buy_price' => 740.00],
            ['symbol' => 'IWDA.AS', 'name' => 'iShares MSCI World ETF', 'exchange' => 'AMS', 'quantity' => 20, 'avg_buy_price' => 85.50],
        ];

        foreach ($positions as $pos) {
            Position::create(array_merge($pos, [
                'portfolio_id' => $portfolio->id,
                'user_id' => $user->id,
                'is_open' => true,
            ]));
        }

        // Watchlist
        $watchSymbols = [
            ['symbol' => 'TSLA', 'name' => 'Tesla Inc.', 'exchange' => 'NASDAQ', 'target_price' => 200.00],
            ['symbol' => 'AMZN', 'name' => 'Amazon.com Inc.', 'exchange' => 'NASDAQ', 'target_price' => 180.00],
            ['symbol' => 'SAN.PA', 'name' => 'Sanofi', 'exchange' => 'EPA', 'target_price' => 95.00],
        ];

        foreach ($watchSymbols as $w) {
            Watchlist::create(array_merge($w, ['user_id' => $user->id]));
        }
    }
}
