<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fokusplan_goals', function (Blueprint $table) {
            // SMART-Felder (siehe Issue #826) — null = "nicht erfasst", kein separater Status nötig
            $table->text('smart_specific')->nullable()->after('description');
            $table->text('smart_measurable')->nullable()->after('smart_specific');
            $table->text('smart_achievable')->nullable()->after('smart_measurable');
            $table->text('smart_relevant')->nullable()->after('smart_achievable');
            $table->text('smart_timebound')->nullable()->after('smart_relevant');
        });
    }

    public function down(): void
    {
        Schema::table('fokusplan_goals', function (Blueprint $table) {
            $table->dropColumn([
                'smart_specific',
                'smart_measurable',
                'smart_achievable',
                'smart_relevant',
                'smart_timebound',
            ]);
        });
    }
};
