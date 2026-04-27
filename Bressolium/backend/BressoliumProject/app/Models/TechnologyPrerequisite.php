<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TechnologyPrerequisite extends Model
{
    use HasUuids;

    protected $fillable = [
        'technology_id',
        'prereq_type',
        'prereq_id',
        'quantity',
    ];

    /**
     * Tecnología a la que pertenece este prerequisito.
     */
    public function technology(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Technology::class);
    }
}
