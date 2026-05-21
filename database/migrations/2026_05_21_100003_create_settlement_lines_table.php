<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('settlement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('contributed', 10, 2)->default(0);
            $table->decimal('share', 10, 2)->default(0);
            $table->decimal('net', 10, 2)->default(0);
            $table->unsignedInteger('kilometers')->default(0);
            $table->timestamps();
            $table->unique(['settlement_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_lines');
    }
};
