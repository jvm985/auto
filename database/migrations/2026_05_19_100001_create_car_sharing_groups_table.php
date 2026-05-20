<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_sharing_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('car_sharing_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_sharing_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['car_sharing_group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_sharing_group_user');
        Schema::dropIfExists('car_sharing_groups');
    }
};
