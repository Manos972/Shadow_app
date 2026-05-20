<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_data_cache', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20)->unique();
            $table->string('name')->nullable();
            $table->string('exchange')->nullable();
            $table->decimal('price', 15, 4)->nullable();
            $table->decimal('previous_close', 15, 4)->nullable();
            $table->decimal('open', 15, 4)->nullable();
            $table->decimal('high', 15, 4)->nullable();
            $table->decimal('low', 15, 4)->nullable();
            $table->bigInteger('volume')->nullable();
            $table->decimal('market_cap', 20, 2)->nullable();
            $table->decimal('pe_ratio', 10, 2)->nullable();
            $table->decimal('eps', 10, 4)->nullable();
            $table->decimal('dividend_yield', 8, 4)->nullable();
            $table->decimal('beta', 8, 4)->nullable();
            $table->decimal('week_52_high', 15, 4)->nullable();
            $table->decimal('week_52_low', 15, 4)->nullable();
            $table->json('price_history')->nullable();
            $table->decimal('rsi_14', 8, 4)->nullable();
            $table->decimal('sma_20', 15, 4)->nullable();
            $table->decimal('sma_50', 15, 4)->nullable();
            $table->decimal('sma_200', 15, 4)->nullable();
            $table->decimal('macd', 10, 4)->nullable();
            $table->decimal('macd_signal', 10, 4)->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_data_cache');
    }
};
