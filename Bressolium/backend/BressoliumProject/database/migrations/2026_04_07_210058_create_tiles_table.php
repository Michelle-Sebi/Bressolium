<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('game_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('tile_type_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('assigned_player')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('coord_x');
            $table->integer('coord_y');
            $table->boolean('explored')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiles');
    }
};