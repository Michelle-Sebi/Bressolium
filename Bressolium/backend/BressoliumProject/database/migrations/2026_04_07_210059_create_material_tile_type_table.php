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
        Schema::create('material_tile_type', function (Blueprint $table) {
            $table->foreignUuid('tile_type_id')->constrained()->cascadeOnDelete();
            $table->uuid('material_id'); // FK será añadida en T14
            $table->integer('quantity');
            $table->primary(['tile_type_id', 'material_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_tile_type');
    }
};