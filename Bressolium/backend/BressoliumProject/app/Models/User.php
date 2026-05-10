<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Un usuario puede pertenecer a muchas partidas (N:M).
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class)->withPivot('is_afk')->withTimestamps();
    }

    /**
     * Relación con las jornadas (M:N via round_user).
     */
    public function rounds(): BelongsToMany
    {
        return $this->belongsToMany(Round::class)->withPivot('actions_spent')->withTimestamps();
    }

    /**
     * Relación: un usuario puede emitir muchos votos.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
