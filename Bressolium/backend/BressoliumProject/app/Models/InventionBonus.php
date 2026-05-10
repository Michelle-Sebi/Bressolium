<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventionBonus extends Model
{
    use HasUuids;

    protected $fillable = [
        'invention_id',
        'bonus_type',
        'bonus_value',
        'bonus_target',
    ];

    /**
     * Invento al que pertenece este bonificador.
     */
    public function invention(): BelongsTo
    {
        return $this->belongsTo(Invention::class);
    }
}
