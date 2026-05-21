<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'car_sharing_group_id',
    'period_start',
    'period_end',
    'total_cost',
    'total_km',
    'participant_count',
    'share_per_participant',
    'closed_by_user_id',
])]
class Settlement extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'total_cost' => 'decimal:2',
            'share_per_participant' => 'decimal:2',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CarSharingGroup::class, 'car_sharing_group_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SettlementLine::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function mileageEntries(): HasMany
    {
        return $this->hasMany(MileageEntry::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }
}
