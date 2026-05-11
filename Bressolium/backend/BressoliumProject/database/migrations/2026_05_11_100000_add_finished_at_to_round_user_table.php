<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('round_user', function (Blueprint $table) {
            $table->timestamp('finished_at')->nullable()->after('actions_spent');
        });
    }

    public function down(): void
    {
        Schema::table('round_user', function (Blueprint $table) {
            $table->dropColumn('finished_at');
        });
    }
};
