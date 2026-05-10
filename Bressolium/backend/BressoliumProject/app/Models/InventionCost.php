<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventionCost extends Model
{
    use HasUuids;

    protected $fillable = [
        'invention_id',
        'resource_id',
        'quantity',
    ];

    /**
     * Invento al que pertenece este coste.
     */
    public function invention(): BelongsTo
    {
        return $this->belongsTo(Invention::class);
    }

    /**
     * Material (recurso) que se consume como coste.
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'resource_id');
    }
}
