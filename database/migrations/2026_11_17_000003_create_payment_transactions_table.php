<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('received_at');
            $table->string('method', 40)->default('GCash');
            $table->string('reference_number', 120)->nullable();
            $table->timestamps();
            $table->index('received_at');
        });
    }
    public function down(): void { Schema::dropIfExists('payment_transactions'); }
};
