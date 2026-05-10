<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'tier',
        'group',
    ];

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_material')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
