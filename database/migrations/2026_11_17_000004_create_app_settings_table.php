<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('receiver_name')->default('Account Owner');
            $table->string('gcash_number', 40)->nullable();
            $table->decimal('default_share', 12, 2)->default(1150);
            $table->unsignedTinyInteger('reminder_days')->default(3);
            $table->text('increase_reason')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('app_settings'); }
};
