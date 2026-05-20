<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('symbol', 20);
            $table->string('name')->nullable();
            $table->enum('type', ['price_above', 'price_below', 'change_percent_above', 'change_percent_below', 'rsi_above', 'rsi_below']);
            $table->decimal('threshold', 15, 4);
            $table->boolean('is_active')->default(true);
            $table->boolean('notify_email')->default(true);
            $table->timestamp('triggered_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'symbol']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
    }
};
