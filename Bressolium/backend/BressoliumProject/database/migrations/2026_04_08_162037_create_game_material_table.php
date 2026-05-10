<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_material', function (Blueprint $table) {
            $table->id();
            $table->uuid('game_id');
            $table->uuid('material_id');
            $table->integer('quantity')->default(0);

            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_material');
    }
};
