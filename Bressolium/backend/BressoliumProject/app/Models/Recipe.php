<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Recipe extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'recipeable_id',
        'recipeable_type',
        'material_id',
        'quantity',
    ];

    public function recipeable(): MorphTo
    {
        return $this->morphTo();
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
