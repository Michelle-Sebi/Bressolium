<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_invention', function (Blueprint $table) {
            $table->id();
            $table->uuid('game_id');
            $table->uuid('invention_id');
            $table->boolean('is_active')->default(false);
            
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
            $table->foreign('invention_id')->references('id')->on('inventions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_invention');
    }
};
