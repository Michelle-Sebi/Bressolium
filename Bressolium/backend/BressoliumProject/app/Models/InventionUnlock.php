<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventionUnlock extends Model
{
    use HasUuids;

    protected $fillable = [
        'invention_id',
        'unlock_type',
        'unlock_id',
    ];

    /**
     * Invento al que pertenece este desbloqueo.
     */
    public function invention(): BelongsTo
    {
        return $this->belongsTo(Invention::class);
    }
}
