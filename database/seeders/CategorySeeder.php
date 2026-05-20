<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $categories = [
            // Revenus
            ['type' => 'income', 'name' => 'Salaire', 'icon' => '💼', 'color' => '#10b981'],
            ['type' => 'income', 'name' => 'Freelance', 'icon' => '💻', 'color' => '#06b6d4'],
            ['type' => 'income', 'name' => 'Dividendes', 'icon' => '📈', 'color' => '#8b5cf6'],
            ['type' => 'income', 'name' => 'Loyers', 'icon' => '🏠', 'color' => '#f59e0b'],
            ['type' => 'income', 'name' => 'Remboursements', 'icon' => '↩️', 'color' => '#64748b'],
            ['type' => 'income', 'name' => 'Autres revenus', 'icon' => '💰', 'color' => '#84cc16'],
            // Dépenses
            ['type' => 'expense', 'name' => 'Logement', 'icon' => '🏠', 'color' => '#ef4444', 'monthly_budget' => 1200],
            ['type' => 'expense', 'name' => 'Alimentation', 'icon' => '🛒', 'color' => '#f97316', 'monthly_budget' => 400],
            ['type' => 'expense', 'name' => 'Transports', 'icon' => '🚗', 'color' => '#eab308', 'monthly_budget' => 200],
            ['type' => 'expense', 'name' => 'Santé', 'icon' => '🏥', 'color' => '#ec4899', 'monthly_budget' => 100],
            ['type' => 'expense', 'name' => 'Loisirs', 'icon' => '🎮', 'color' => '#8b5cf6', 'monthly_budget' => 200],
            ['type' => 'expense', 'name' => 'Restaurants', 'icon' => '🍽️', 'color' => '#f59e0b', 'monthly_budget' => 150],
            ['type' => 'expense', 'name' => 'Abonnements', 'icon' => '📱', 'color' => '#3b82f6', 'monthly_budget' => 80],
            ['type' => 'expense', 'name' => 'Vêtements', 'icon' => '👕', 'color' => '#06b6d4', 'monthly_budget' => 100],
            ['type' => 'expense', 'name' => 'Épargne', 'icon' => '🐷', 'color' => '#10b981', 'monthly_budget' => 500],
            ['type' => 'expense', 'name' => 'Investissements', 'icon' => '📊', 'color' => '#84cc16', 'monthly_budget' => 300],
            ['type' => 'expense', 'name' => 'Taxes & Impôts', 'icon' => '🏛️', 'color' => '#64748b'],
            ['type' => 'expense', 'name' => 'Autres', 'icon' => '📦', 'color' => '#94a3b8'],
        ];

        foreach ($categories as $cat) {
            Category::create(array_merge($cat, ['user_id' => $user->id]));
        }
    }
}
