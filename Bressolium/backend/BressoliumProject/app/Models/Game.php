<?php

namespace App\Models;

use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * Relación con los usuarios (N:M).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('is_afk')->withTimestamps();
    }

    /**
     * Relación: una partida tiene muchas jornadas.
     */
    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'game_material')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class, 'game_technology')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function inventions(): BelongsToMany
    {
        return $this->belongsToMany(Invention::class, 'game_invention')
            ->withPivot('is_active', 'quantity')
            ->withTimestamps();
    }
}
