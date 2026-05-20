<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dividends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portfolio_id')->nullable()->constrained()->nullOnDelete();
            $table->string('symbol', 20);
            $table->string('name')->nullable();
            $table->decimal('amount_per_share', 10, 4);
            $table->decimal('shares', 15, 6);
            $table->decimal('total_amount', 15, 2)->storedAs('amount_per_share * shares');
            $table->string('currency', 3)->default('CAD');
            $table->enum('type', ['ordinary', 'eligible', 'return_of_capital', 'capital_gain'])->default('eligible');
            $table->date('ex_date');
            $table->date('pay_date')->nullable();
            $table->boolean('is_drip')->default(false);
            $table->decimal('drip_shares', 10, 6)->nullable();
            $table->decimal('drip_price', 10, 4)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'symbol']);
            $table->index('ex_date');
        });

        Schema::create('portfolio_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->decimal('total_value', 15, 2);
            $table->decimal('total_cost', 15, 2);
            $table->decimal('unrealized_pnl', 15, 2);
            $table->decimal('realized_pnl', 15, 2)->default(0);
            $table->decimal('dividends_ytd', 15, 2)->default(0);
            $table->json('positions_snapshot')->nullable();
            $table->timestamps();
            $table->unique(['portfolio_id', 'snapshot_date']);
        });

        Schema::create('financial_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['savings', 'investment', 'debt_payoff', 'emergency_fund', 'retirement', 'tfsa_max', 'rrsp_max', 'custom']);
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->decimal('monthly_contribution', 15, 2)->default(0);
            $table->date('target_date')->nullable();
            $table->string('icon')->default('🎯');
            $table->string('color')->default('#3b82f6');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('financial_goals');
        Schema::dropIfExists('portfolio_snapshots');
        Schema::dropIfExists('dividends');
    }
};
