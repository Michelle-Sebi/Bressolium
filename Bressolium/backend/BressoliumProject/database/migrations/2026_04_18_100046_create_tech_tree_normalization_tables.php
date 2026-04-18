<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('technology_prerequisites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('technology_id')->constrained()->cascadeOnDelete();
            $table->string('prereq_type');
            $table->uuid('prereq_id');
            $table->timestamps();
        });

        Schema::create('invention_prerequisites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invention_id')->constrained()->cascadeOnDelete();
            $table->string('prereq_type');
            $table->uuid('prereq_id');
            $table->timestamps();
        });

        Schema::create('invention_costs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invention_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('resource_id')->constrained('materials')->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });

        Schema::create('technology_bonuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('technology_id')->constrained()->cascadeOnDelete();
            $table->string('bonus_type');
            $table->float('bonus_value');
            $table->string('bonus_target');
            $table->timestamps();
        });

        Schema::create('invention_bonuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invention_id')->constrained()->cascadeOnDelete();
            $table->string('bonus_type');
            $table->float('bonus_value');
            $table->string('bonus_target');
            $table->timestamps();
        });

        Schema::create('technology_unlocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('technology_id')->constrained()->cascadeOnDelete();
            $table->enum('unlock_type', ['technology', 'invention', 'tile_level']);
            $table->uuid('unlock_id')->nullable();
            $table->timestamps();
        });

        Schema::create('invention_unlocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invention_id')->constrained()->cascadeOnDelete();
            $table->enum('unlock_type', ['technology', 'invention', 'tile_level']);
            $table->uuid('unlock_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invention_unlocks');
        Schema::dropIfExists('technology_unlocks');
        Schema::dropIfExists('invention_bonuses');
        Schema::dropIfExists('technology_bonuses');
        Schema::dropIfExists('invention_costs');
        Schema::dropIfExists('invention_prerequisites');
        Schema::dropIfExists('technology_prerequisites');
    }
};
