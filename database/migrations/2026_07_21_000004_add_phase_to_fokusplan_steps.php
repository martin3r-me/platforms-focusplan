<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->foreignId('fokusplan_phase_id')
                ->nullable()
                ->after('fokusplan_plan_id')
                ->constrained('fokusplan_phases')
                ->nullOnDelete();

            $table->index(['fokusplan_phase_id', 'position'], 'fokusplan_steps_phase_position_idx');
        });
    }

    public function down(): void
    {
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->dropIndex('fokusplan_steps_phase_position_idx');
            $table->dropConstrainedForeignId('fokusplan_phase_id');
        });
    }
};
