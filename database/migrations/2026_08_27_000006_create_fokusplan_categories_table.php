<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Uid\UuidV7;

return new class extends Migration
{
    /**
     * Sechs Stoßrichtungen aus dem Prototyp (View "Ausrichtung", Konstante ORIENTATIONS).
     * Siehe Issue #831.
     */
    public const DEFAULT_TITLES = [
        'Kosteneffizienz & Produktivität',
        'Digitalisierung & Systeme',
        'Mitarbeiter & Nachfolge',
        'Kundenzufriedenheit & Qualität',
        'Umsatzwachstum & neue Geschäftsfelder',
        'Compliance & Risikomanagement',
    ];

    public function up(): void
    {
        Schema::create('fokusplan_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->string('title');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['team_id', 'title']);
        });

        // Bestehende Teams bekommen die sechs Stammdaten-Kategorien sofort;
        // FokusplanCategory::ensureDefaultsForTeam() deckt neue Teams danach ab.
        $now = now();
        $teamIds = DB::table('teams')->pluck('id');

        foreach ($teamIds as $teamId) {
            foreach (self::DEFAULT_TITLES as $position => $title) {
                DB::table('fokusplan_categories')->insert([
                    'uuid' => (string) UuidV7::generate(),
                    'team_id' => $teamId,
                    'title' => $title,
                    'position' => $position,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fokusplan_categories');
    }
};
