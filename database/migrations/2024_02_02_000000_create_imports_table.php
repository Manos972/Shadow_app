<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('filename');
            $table->enum('type', ['bank_csv', 'portfolio_csv', 'ofx', 'questrade', 'wealthsimple', 'td_webbroker', 'rbc_direct', 'ibkr']);
            $table->enum('status', ['pending', 'mapping', 'preview', 'completed', 'failed'])->default('pending');
            $table->integer('total_rows')->default(0);
            $table->integer('imported_rows')->default(0);
            $table->integer('skipped_rows')->default(0);
            $table->integer('duplicate_rows')->default(0);
            $table->json('column_mapping')->nullable();
            $table->json('preview_data')->nullable();
            $table->text('error_message')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('imports'); }
};
