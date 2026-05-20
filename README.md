# Shadow Budget & Portfolio Manager

Application Laravel 11 de gestion de budget personnel et de portefeuille boursier.

## Stack technique
- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Livewire 3, Alpine.js, Tailwind CSS
- **Base de données**: SQLite (par défaut) ou MySQL
- **API**: Yahoo Finance (données boursières temps réel)

## Fonctionnalités

### Budget
- Gestion de plusieurs comptes (courant, épargne, espèces, crédit)
- Transactions avec catégories personnalisables
- Budgets mensuels par catégorie
- Transactions récurrentes automatiques
- Rapports mensuels/annuels avec graphiques

### Portefeuille boursier
- Gestion de plusieurs portefeuilles
- Suivi des positions (achat/vente)
- Prix en temps quasi-réel via Yahoo Finance
- Calcul P&L (plus/moins-values réalisées et latentes)
- Indicateurs techniques : RSI, SMA 20/50/200, MACD
- Alertes de prix (email)
- Liste de surveillance (watchlist)
- Performance vs S&P 500

## Installation

```bash
git clone https://github.com/manos972/shadow_app.git
cd shadow_app
git checkout claude/laravel-budget-portfolio-app-7XkHK

# Installer les dépendances PHP
composer install

# Installer les dépendances Node
npm install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données SQLite (créée automatiquement)
touch database/database.sqlite
php artisan migrate --seed

# Compiler les assets
npm run build

# Lancer le serveur
php artisan serve
```

## Accès

- URL: http://localhost:8000
- Compte démo créé par le seeder : **demo@shadow.app** / **password**

## Commandes Artisan

```bash
# Récupérer les cours boursiers
php artisan market:fetch

# Vérifier les alertes de prix
php artisan alerts:process

# Traiter les transactions récurrentes
php artisan recurring:process

# Lancer le scheduler (production)
php artisan schedule:work
```

## Configuration des données boursières

Par défaut, l'app utilise Yahoo Finance (gratuit, aucune clé requise).
Pour des données étendues, configurez `ALPHA_VANTAGE_API_KEY` dans `.env`.
