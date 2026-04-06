<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Vote extends Model
{
    /** @use HasFactory<\Database\Factories\VoteFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'round_id',
        'user_id',
        'technology_id',
    ];

    /**
     * Relación: un voto pertenece a una jornada.
     */
    public function round(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    /**
     * Relación: un voto lo emite un usuario.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
