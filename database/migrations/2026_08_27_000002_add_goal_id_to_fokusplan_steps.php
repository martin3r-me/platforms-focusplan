<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->foreignId('fokusplan_goal_id')
                ->nullable()
                ->after('fokusplan_phase_id')
                ->constrained('fokusplan_goals')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fokusplan_goal_id');
        });
    }
};
