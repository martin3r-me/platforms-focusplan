<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fokusplan_goals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('fokusplan_plan_id')->constrained('fokusplan_plans')->onDelete('cascade');
            $table->foreignId('fokusplan_phase_id')->constrained('fokusplan_phases')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();

            // Steuerungsblock (siehe Issue #827)
            $table->string('responsible')->nullable();
            $table->text('kpi')->nullable();
            $table->decimal('potential', 12, 2)->nullable();
            $table->string('impact')->nullable();
            $table->text('risk_note')->nullable();
            $table->text('diagnosis')->nullable();

            $table->unsignedInteger('position')->default(0);
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['fokusplan_phase_id', 'position'], 'fokusplan_goals_phase_position_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fokusplan_goals');
    }
};
