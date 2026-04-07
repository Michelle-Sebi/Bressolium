<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Tile extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'game_id',
        'tile_type_id',
        'assigned_player',
        'coord_x',
        'coord_y',
        'explored'
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function type()
    {
        return $this->belongsTo(TileType::class , 'tile_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class , 'assigned_player');
    }
}