<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('symbol', 20);
            $table->enum('type', ['buy', 'sell']);
            $table->decimal('quantity', 15, 6);
            $table->decimal('price', 15, 4);
            $table->decimal('fees', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->storedAs('quantity * price + fees');
            $table->datetime('executed_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['portfolio_id', 'symbol']);
            $table->index('executed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
