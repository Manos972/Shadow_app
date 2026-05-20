# Shadow Finance — Budget & Portefeuille Boursier

Application Laravel 12 complète de gestion de budget personnel et de portefeuille boursier canadien.

> **Note Laravel 13** : Si Laravel 13 est disponible sur votre machine, changez `"^12.0"` par `"^13.0"` dans `composer.json` — l'app est compatible.

## Stack technique
- **Backend** : Laravel 12, PHP 8.3+
- **Frontend** : Livewire 3, Alpine.js, Tailwind CSS 3
- **Base de données** : SQLite (défaut) ou MySQL/PostgreSQL
- **API boursière** : Yahoo Finance (gratuit, sans clé API)
- **Multi-tenant** : Espaces partagés famille/partenaire

## Fonctionnalités

### 💰 Budget
- Comptes CÉLI, REER, FHSA, chèques, épargne, espèces
- Transactions avec catégorisation automatique (marchands canadiens)
- Budgets mensuels par catégorie avec alertes dépassement
- Transactions récurrentes (loyer, abonnements, salaire)
- Rapports mensuels/annuels avec graphiques
- **Import CSV/OFX** : RBC, TD, BMO, Scotiabank, Desjardins, Questrade, Wealthsimple

### 📈 Portefeuille boursier
- Actions TSX (.TO), NASDAQ, NYSE
- Prix quasi-temps réel via Yahoo Finance
- P&L latent et réalisé
- Indicateurs techniques : RSI(14), SMA 20/50/200, MACD
- Alertes prix (par email)
- Watchlist avec prix objectifs et upside %
- Analyse de l'allocation

### 👥 Multi-tenant
- Créez votre espace financier personnel
- Invitez votre partenaire/famille (max 5 personnes)
- Rôles : Propriétaire, Admin, Membre, Lecteur

## Installation rapide

```bash
git clone https://github.com/manos972/shadow_app.git
cd shadow_app
git checkout claude/laravel-budget-portfolio-app-7XkHK

composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed

npm run build
php artisan serve
```

**URL** : http://localhost:8000  
**Compte démo** : `demo@shadow.app` / `password`

## Scheduler (transactions récurrentes + cours boursiers)

```bash
# Développement
php artisan schedule:work

# Production (crontab)
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

## Commandes Artisan

```bash
php artisan market:fetch          # Récupère les cours boursiers
php artisan alerts:process        # Vérifie les alertes de prix
php artisan recurring:process     # Traite les transactions récurrentes
```

## Mise à niveau Laravel 13

Si Laravel 13 est sorti et stable :
```bash
# Dans composer.json, modifier :
"laravel/framework": "^13.0"

# Puis :
composer update laravel/framework
php artisan migrate  # Si nouvelles migrations L13
```
