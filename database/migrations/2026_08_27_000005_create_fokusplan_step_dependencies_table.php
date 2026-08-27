<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fokusplan_step_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fokusplan_step_id')->constrained('fokusplan_steps')->onDelete('cascade');
            $table->foreignId('depends_on_step_id')->constrained('fokusplan_steps')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['fokusplan_step_id', 'depends_on_step_id'], 'fokusplan_step_deps_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fokusplan_step_dependencies');
    }
};
