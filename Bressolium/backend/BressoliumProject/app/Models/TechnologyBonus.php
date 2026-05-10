<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnologyBonus extends Model
{
    use HasUuids;

    protected $fillable = [
        'technology_id',
        'bonus_type',
        'bonus_value',
        'bonus_target',
    ];

    /**
     * Tecnología a la que pertenece este bonificador.
     */
    public function technology(): BelongsTo
    {
        return $this->belongsTo(Technology::class);
    }
}
