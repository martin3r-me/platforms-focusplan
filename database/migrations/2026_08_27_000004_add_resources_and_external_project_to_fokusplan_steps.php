<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->json('resources')->nullable()->after('supporters');
            $table->string('external_project_ref')->nullable()->after('resources');
        });
    }

    public function down(): void
    {
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->dropColumn(['resources', 'external_project_ref']);
        });
    }
};
