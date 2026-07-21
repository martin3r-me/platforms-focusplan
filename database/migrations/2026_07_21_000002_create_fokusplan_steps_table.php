<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fokusplan_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('fokusplan_plan_id')->constrained('fokusplan_plans')->onDelete('cascade');
            $table->string('title');
            $table->text('details')->nullable();
            $table->string('lead')->nullable();
            $table->string('kennzahl')->nullable();
            $table->string('deadline')->nullable();
            $table->enum('status', ['open', 'in_progress', 'done'])->default('open');
            $table->unsignedInteger('position')->default(0);
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['fokusplan_plan_id', 'position'], 'fokusplan_steps_plan_position_idx');
            $table->index(['fokusplan_plan_id', 'status'], 'fokusplan_steps_plan_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fokusplan_steps');
    }
};
