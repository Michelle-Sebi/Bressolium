<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TechnologyUnlock extends Model
{
    use HasUuids;

    protected $fillable = [
        'technology_id',
        'unlock_type',
        'unlock_id',
    ];

    /**
     * Tecnología a la que pertenece este desbloqueo.
     */
    public function technology(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Technology::class);
    }
}
