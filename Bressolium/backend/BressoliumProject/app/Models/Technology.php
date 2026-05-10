<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Technology extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'prerequisite_id',
    ];

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Technology::class, 'prerequisite_id');
    }

    public function inventions(): HasMany
    {
        return $this->hasMany(Invention::class);
    }

    public function recipes(): MorphMany
    {
        return $this->morphMany(Recipe::class, 'recipeable');
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_technology')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * Prerequisitos necesarios para desbloquear esta tecnología.
     */
    public function technologyPrerequisites(): HasMany
    {
        return $this->hasMany(TechnologyPrerequisite::class);
    }

    /**
     * Bonificadores que otorga esta tecnología al equipo.
     */
    public function technologyBonuses(): HasMany
    {
        return $this->hasMany(TechnologyBonus::class);
    }

    /**
     * Desbloqueos (tecnologías, inventos o niveles de casilla) que activa esta tecnología.
     */
    public function technologyUnlocks(): HasMany
    {
        return $this->hasMany(TechnologyUnlock::class);
    }
}
