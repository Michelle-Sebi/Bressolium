<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Game;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

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
    public function games(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Game::class)->withPivot('is_afk')->withTimestamps();
    }

    /**
     * Relación con las jornadas (M:N via round_user).
     */
    public function rounds(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Round::class)->withPivot('actions_spent')->withTimestamps();
    }

    /**
     * Relación: un usuario puede emitir muchos votos.
     */
    public function votes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Vote::class);
    }
}