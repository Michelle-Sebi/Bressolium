<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TileType extends Model
{
    use HasUuids;

    protected $fillable = ['id', 'name', 'level'];

    public function tiles()
    {
        return $this->hasMany(Tile::class);
    }
}