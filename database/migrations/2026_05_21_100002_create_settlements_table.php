<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_sharing_group_id')->constrained()->cascadeOnDelete();
            $table->date('period_start')->nullable();
            $table->date('period_end');
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->unsignedInteger('total_km')->default(0);
            $table->unsignedInteger('participant_count')->default(0);
            $table->decimal('share_per_participant', 10, 2)->default(0);
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['car_sharing_group_id', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
