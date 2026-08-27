<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * n:m-Zuordnung Ziel <-> Kategorie (Issue #831): ein Ziel kann auf mehrere
     * Stoßrichtungen einzahlen.
     */
    public function up(): void
    {
        Schema::create('fokusplan_goal_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fokusplan_goal_id')->constrained('fokusplan_goals')->onDelete('cascade');
            $table->foreignId('fokusplan_category_id')->constrained('fokusplan_categories')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['fokusplan_goal_id', 'fokusplan_category_id'], 'fokusplan_goal_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fokusplan_goal_category');
    }
};
