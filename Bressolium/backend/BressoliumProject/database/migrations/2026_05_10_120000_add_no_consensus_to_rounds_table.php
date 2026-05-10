<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->boolean('no_consensus')->nullable()->after('ended_at');
            $table->foreignUuid('last_built_invention_id')->nullable()->after('no_consensus')
                ->constrained('inventions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->dropForeign(['last_built_invention_id']);
            $table->dropColumn(['no_consensus', 'last_built_invention_id']);
        });
    }
};
