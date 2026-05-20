<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::orderBy('id')->first();
        if (!$user) return;

        $categories = [
            // Revenus
            ['type' => 'income', 'name' => 'Salaire', 'icon' => '💼', 'color' => '#10b981'],
            ['type' => 'income', 'name' => 'Freelance / Contrat', 'icon' => '💻', 'color' => '#06b6d4'],
            ['type' => 'income', 'name' => 'Dividendes', 'icon' => '📈', 'color' => '#8b5cf6'],
            ['type' => 'income', 'name' => 'Intérêts CÉLI', 'icon' => '🏦', 'color' => '#10b981'],
            ['type' => 'income', 'name' => 'Loyers / Sous-location', 'icon' => '🏠', 'color' => '#f59e0b'],
            ['type' => 'income', 'name' => 'Remboursements', 'icon' => '↩️', 'color' => '#64748b'],
            ['type' => 'income', 'name' => 'Bonus / Prime', 'icon' => '🎯', 'color' => '#ec4899'],
            ['type' => 'income', 'name' => 'Autres revenus', 'icon' => '💰', 'color' => '#84cc16'],
            // Dépenses
            ['type' => 'expense', 'name' => 'Loyer / Hypothèque', 'icon' => '🏠', 'color' => '#ef4444', 'monthly_budget' => 1800],
            ['type' => 'expense', 'name' => 'Épicerie', 'icon' => '🛒', 'color' => '#f97316', 'monthly_budget' => 600],
            ['type' => 'expense', 'name' => 'Restaurants / Livraison', 'icon' => '🍽️', 'color' => '#f59e0b', 'monthly_budget' => 200],
            ['type' => 'expense', 'name' => 'Transport / Essence', 'icon' => '🚗', 'color' => '#eab308', 'monthly_budget' => 300],
            ['type' => 'expense', 'name' => 'Santé / Pharmacie', 'icon' => '🏥', 'color' => '#ec4899', 'monthly_budget' => 150],
            ['type' => 'expense', 'name' => 'Loisirs / Sorties', 'icon' => '🎮', 'color' => '#8b5cf6', 'monthly_budget' => 250],
            ['type' => 'expense', 'name' => 'Abonnements (Bell/Rogers/Netflix)', 'icon' => '📱', 'color' => '#3b82f6', 'monthly_budget' => 120],
            ['type' => 'expense', 'name' => 'Vêtements', 'icon' => '👕', 'color' => '#06b6d4', 'monthly_budget' => 150],
            ['type' => 'expense', 'name' => 'Épargne REER', 'icon' => '🐷', 'color' => '#10b981', 'monthly_budget' => 500],
            ['type' => 'expense', 'name' => 'Cotisation CÉLI', 'icon' => '📊', 'color' => '#84cc16', 'monthly_budget' => 500],
            ['type' => 'expense', 'name' => 'Assurances', 'icon' => '🛡️', 'color' => '#6b7280', 'monthly_budget' => 200],
            ['type' => 'expense', 'name' => 'Impôts provinciaux/fédéraux', 'icon' => '🏛️', 'color' => '#64748b'],
            ['type' => 'expense', 'name' => 'Éducation / Formation', 'icon' => '📚', 'color' => '#0ea5e9', 'monthly_budget' => 100],
            ['type' => 'expense', 'name' => 'Entretien maison/auto', 'icon' => '🔧', 'color' => '#92400e', 'monthly_budget' => 200],
            ['type' => 'expense', 'name' => 'Cadeaux / Dons', 'icon' => '🎁', 'color' => '#be185d'],
            ['type' => 'expense', 'name' => 'Divers', 'icon' => '📦', 'color' => '#94a3b8'],
        ];

        foreach ($categories as $cat) {
            if (!Category::where('user_id', $user->id)->where('name', $cat['name'])->exists()) {
                Category::create(array_merge($cat, ['user_id' => $user->id]));
            }
        }
    }
}
