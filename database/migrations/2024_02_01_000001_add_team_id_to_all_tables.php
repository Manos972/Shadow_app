<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        $tables = ['accounts', 'categories', 'transactions', 'recurring_transactions', 'portfolios', 'stock_alerts', 'watchlists'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('team_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void {
        $tables = ['accounts', 'categories', 'transactions', 'recurring_transactions', 'portfolios', 'stock_alerts', 'watchlists'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeignIfExists(['team_id']);
                $t->dropColumnIfExists('team_id');
            });
        }
    }
};
