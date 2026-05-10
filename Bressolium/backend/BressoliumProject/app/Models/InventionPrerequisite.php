<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventionPrerequisite extends Model
{
    use HasUuids;

    protected $fillable = [
        'invention_id',
        'prereq_type',
        'prereq_id',
        'quantity',
    ];

    /**
     * Invento al que pertenece este prerequisito.
     */
    public function invention(): BelongsTo
    {
        return $this->belongsTo(Invention::class);
    }
}
