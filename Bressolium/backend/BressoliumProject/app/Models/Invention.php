<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Invention extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'technology_id',
        'is_final',
    ];

    protected $casts = [
        'is_final' => 'boolean',
    ];

    public function technology(): BelongsTo
    {
        return $this->belongsTo(Technology::class);
    }

    public function recipes(): MorphMany
    {
        return $this->morphMany(Recipe::class, 'recipeable');
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_invention')
            ->withPivot('is_active', 'quantity')
            ->withTimestamps();
    }

    /**
     * Prerequisitos necesarios para poder construir este invento.
     */
    public function inventionPrerequisites(): HasMany
    {
        return $this->hasMany(InventionPrerequisite::class);
    }

    /**
     * Costes en materiales (recursos) que se consumen al construir este invento.
     */
    public function inventionCosts(): HasMany
    {
        return $this->hasMany(InventionCost::class);
    }

    /**
     * Bonificadores que activa este invento para el equipo.
     */
    public function inventionBonuses(): HasMany
    {
        return $this->hasMany(InventionBonus::class);
    }

    /**
     * Desbloqueos (tecnologías, inventos o niveles de casilla) que activa este invento.
     */
    public function inventionUnlocks(): HasMany
    {
        return $this->hasMany(InventionUnlock::class);
    }
}
