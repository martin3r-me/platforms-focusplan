<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Strukturiertes Potenzial je Maßnahme (Issue #831): Grundlage für die
     * Hochrechnung Step -> Ziel -> Bereich -> Kategorie auf der Ausrichtungsseite.
     * `kennzahl` (Freitext) bleibt unverändert für die bisherige Detailbeschreibung.
     */
    public function up(): void
    {
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->decimal('potential_value', 12, 2)->nullable()->after('kennzahl');
            $table->string('potential_unit')->nullable()->after('potential_value');
        });
    }

    public function down(): void
    {
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->dropColumn(['potential_value', 'potential_unit']);
        });
    }
};
