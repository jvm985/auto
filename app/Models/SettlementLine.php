<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['settlement_id', 'user_id', 'contributed', 'share', 'net', 'kilometers'])]
class SettlementLine extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'contributed' => 'decimal:2',
            'share' => 'decimal:2',
            'net' => 'decimal:2',
            'kilometers' => 'integer',
        ];
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(Settlement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
