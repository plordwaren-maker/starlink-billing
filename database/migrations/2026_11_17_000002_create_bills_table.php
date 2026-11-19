<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('cycle_number')->nullable();
            $table->dateTime('period_start');
            $table->dateTime('due_at');
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->boolean('is_manual')->default(false);
            $table->timestamps();
            $table->unique(['client_id', 'cycle_number']);
            $table->index('due_at');
        });
    }
    public function down(): void { Schema::dropIfExists('bills'); }
};
