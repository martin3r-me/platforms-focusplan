<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->json('supporters')->nullable()->after('lead');
        });
    }

    public function down(): void
    {
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->dropColumn('supporters');
        });
    }
};
