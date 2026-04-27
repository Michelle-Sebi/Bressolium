<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * Relación con los usuarios (N:M).
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('is_afk')->withTimestamps();
    }

    /**
     * Relación: una partida tiene muchas jornadas.
     */
    public function rounds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Round::class);
    }

    public function materials(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'game_material')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function technologies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Technology::class, 'game_technology')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function inventions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Invention::class, 'game_invention')
            ->withPivot('is_active', 'quantity')
            ->withTimestamps();
    }
}