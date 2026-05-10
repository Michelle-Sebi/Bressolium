<?php

namespace App\Models;

use Database\Factories\VoteFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    /** @use HasFactory<VoteFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'round_id',
        'user_id',
        'technology_id',
        'invention_id',
    ];

    /**
     * Relación: un voto pertenece a una jornada.
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    /**
     * Relación: un voto lo emite un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
