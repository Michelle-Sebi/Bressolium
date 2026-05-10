<?php

namespace App\Models;

use Database\Factories\RoundFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Round extends Model
{
    /** @use HasFactory<RoundFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'game_id',
        'number',
        'start_date',
        'ended_at',
    ];

    /**
     * Relación: una jornada pertenece a una partida.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Relación con los usuarios (M:N via round_user).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('actions_spent')->withTimestamps();
    }

    /**
     * Relación: una jornada recibe muchos votos.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
