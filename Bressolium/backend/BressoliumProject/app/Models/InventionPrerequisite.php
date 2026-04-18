<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class InventionPrerequisite extends Model
{
    use HasUuids;

    protected $fillable = [
        'invention_id',
        'prereq_type',
        'prereq_id',
    ];

    /**
     * Invento al que pertenece este prerequisito.
     */
    public function invention(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Invention::class);
    }
}
