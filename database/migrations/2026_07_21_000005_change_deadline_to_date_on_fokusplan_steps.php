<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Freitext-Deadline entfernen und als echtes (nullable) Datumsfeld neu anlegen.
        // Bestehende unscharfe Werte ("Ende Q1", "Mai-August" …) lassen sich nicht
        // verlustfrei in ein Datum übersetzen und werden daher verworfen.
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->dropColumn('deadline');
        });

        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->date('deadline')->nullable()->after('kennzahl');
        });
    }

    public function down(): void
    {
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->dropColumn('deadline');
        });

        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->string('deadline')->nullable()->after('kennzahl');
        });
    }
};
