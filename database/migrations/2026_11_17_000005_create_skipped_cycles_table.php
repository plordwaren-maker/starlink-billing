<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('skipped_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('cycle_number');
            $table->timestamps();
            $table->unique(['client_id', 'cycle_number']);
        });
    }
    public function down(): void { Schema::dropIfExists('skipped_cycles'); }
};
