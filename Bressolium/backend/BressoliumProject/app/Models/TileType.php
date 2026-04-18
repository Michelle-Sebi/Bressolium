<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TileType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['id', 'name', 'level', 'base_type'];

    public function tiles()
    {
        return $this->hasMany(Tile::class);
    }

    public function materials()
    {
        return $this->belongsToMany(Material::class, 'material_tile_type')
                    ->withPivot('quantity', 'tech_required', 'invention_required');
    }
}