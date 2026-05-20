<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'google_id', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function carSharingGroups(): BelongsToMany
    {
        return $this->belongsToMany(CarSharingGroup::class)
            ->withPivot('is_admin')
            ->withTimestamps();
    }

    public function isAdminOf(CarSharingGroup $group): bool
    {
        $pivot = $this->carSharingGroups()->find($group->id)?->pivot;

        return (bool) ($pivot?->is_admin ?? false);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
