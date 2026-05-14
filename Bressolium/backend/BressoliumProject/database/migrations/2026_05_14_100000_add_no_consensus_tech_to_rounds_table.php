<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->boolean('no_consensus_tech')->nullable()->after('last_built_invention_id');
            $table->foreignUuid('last_activated_tech_id')->nullable()->after('no_consensus_tech')
                ->constrained('technologies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->dropForeign(['last_activated_tech_id']);
            $table->dropColumn(['no_consensus_tech', 'last_activated_tech_id']);
        });
    }
};
