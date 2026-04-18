<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Invention extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'technology_id',
    ];

    public function technology(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Technology::class);
    }

    public function recipes(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Recipe::class, 'recipeable');
    }

    public function games(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_invention')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }

    /**
     * Prerequisitos necesarios para poder construir este invento.
     */
    public function inventionPrerequisites(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventionPrerequisite::class);
    }

    /**
     * Costes en materiales (recursos) que se consumen al construir este invento.
     */
    public function inventionCosts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventionCost::class);
    }

    /**
     * Bonificadores que activa este invento para el equipo.
     */
    public function inventionBonuses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventionBonus::class);
    }

    /**
     * Desbloqueos (tecnologías, inventos o niveles de casilla) que activa este invento.
     */
    public function inventionUnlocks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventionUnlock::class);
    }
}
