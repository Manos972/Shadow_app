<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('symbol', 20);
            $table->string('name');
            $table->string('exchange')->nullable();
            $table->decimal('quantity', 15, 6);
            $table->decimal('avg_buy_price', 15, 4);
            $table->decimal('current_price', 15, 4)->nullable();
            $table->decimal('previous_close', 15, 4)->nullable();
            $table->timestamp('price_updated_at')->nullable();
            $table->decimal('realized_pnl', 15, 2)->default(0);
            $table->boolean('is_open')->default(true);
            $table->timestamps();

            $table->unique(['portfolio_id', 'symbol']);
            $table->index('symbol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
