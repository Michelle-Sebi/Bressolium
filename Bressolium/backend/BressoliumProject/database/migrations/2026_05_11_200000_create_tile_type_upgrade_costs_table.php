<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tile_type_upgrade_costs', function (Blueprint $table) {
            $table->foreignUuid('tile_type_id')->constrained()->cascadeOnDelete();
            $table->uuid('material_id');
            $table->foreign('material_id')->references('id')->on('materials')->cascadeOnDelete();
            $table->integer('quantity');
            $table->uuid('tech_required')->nullable();
            $table->foreign('tech_required')->references('id')->on('technologies')->nullOnDelete();
            $table->uuid('invention_required')->nullable();
            $table->foreign('invention_required')->references('id')->on('inventions')->nullOnDelete();
            $table->primary(['tile_type_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tile_type_upgrade_costs');
    }
};
