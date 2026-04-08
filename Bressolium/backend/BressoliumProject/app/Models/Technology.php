<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Technology extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'prerequisite_id',
    ];

    public function prerequisite(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Technology::class, 'prerequisite_id');
    }

    public function inventions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invention::class);
    }

    public function recipes(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Recipe::class, 'recipeable');
    }

    public function games(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_technology')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }
}
