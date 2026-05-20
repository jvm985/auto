<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['car_sharing_group_id', 'name', 'brand', 'model', 'license_plate', 'color'])]
class Car extends Model
{
    use HasFactory;

    public function group(): BelongsTo
    {
        return $this->belongsTo(CarSharingGroup::class, 'car_sharing_group_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function displayName(): string
    {
        $parts = array_filter([$this->brand, $this->model]);

        return $parts ? $this->name.' ('.implode(' ', $parts).')' : $this->name;
    }
}
