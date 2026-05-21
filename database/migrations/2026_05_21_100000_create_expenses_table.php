<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('settlement_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->date('incurred_at');
            $table->timestamps();
            $table->index(['car_id', 'settlement_id']);
            $table->index(['user_id', 'settlement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
