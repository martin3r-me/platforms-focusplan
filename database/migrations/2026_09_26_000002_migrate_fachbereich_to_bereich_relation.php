<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Uid\UuidV7;

return new class extends Migration
{
    /**
     * Ersetzt `fokusplan_plans.fachbereich` (Freitext) durch eine echte
     * Zuordnung auf `fokusplan_bereiche` (Issue #825). Bestehende Werte werden
     * je Team dedupliziert in `fokusplan_bereiche` überführt und die Pläne auf
     * die passende Zeile verdrahtet, bevor die alte Spalte fällt — es entsteht
     * also keine Parallelstruktur aus altem Freitext + neuer Relation.
     */
    public function up(): void
    {
        Schema::table('fokusplan_plans', function (Blueprint $table) {
            $table->foreignId('bereich_id')->nullable()->after('fachbereich')
                ->constrained('fokusplan_bereiche')->nullOnDelete();
        });

        $now = now();

        $plans = DB::table('fokusplan_plans')
            ->select('id', 'team_id', 'fachbereich')
            ->whereNotNull('fachbereich')
            ->get();

        // team_id => [trimmed name => bereich_id]
        $bereichIdsByTeamAndName = [];

        foreach ($plans as $plan) {
            $name = trim((string) $plan->fachbereich);
            if ($name === '') {
                continue;
            }

            if (!isset($bereichIdsByTeamAndName[$plan->team_id][$name])) {
                $existingId = DB::table('fokusplan_bereiche')
                    ->where('team_id', $plan->team_id)
                    ->where('name', $name)
                    ->value('id');

                if (!$existingId) {
                    $position = (int) DB::table('fokusplan_bereiche')
                        ->where('team_id', $plan->team_id)
                        ->max('position');

                    $existingId = DB::table('fokusplan_bereiche')->insertGetId([
                        'uuid' => (string) UuidV7::generate(),
                        'team_id' => $plan->team_id,
                        'name' => $name,
                        'position' => $position + 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $bereichIdsByTeamAndName[$plan->team_id][$name] = $existingId;
            }

            DB::table('fokusplan_plans')
                ->where('id', $plan->id)
                ->update(['bereich_id' => $bereichIdsByTeamAndName[$plan->team_id][$name]]);
        }

        Schema::table('fokusplan_plans', function (Blueprint $table) {
            $table->dropColumn('fachbereich');
        });
    }

    public function down(): void
    {
        Schema::table('fokusplan_plans', function (Blueprint $table) {
            $table->string('fachbereich')->nullable()->after('title');
        });

        DB::table('fokusplan_plans')
            ->join('fokusplan_bereiche', 'fokusplan_bereiche.id', '=', 'fokusplan_plans.bereich_id')
            ->update(['fokusplan_plans.fachbereich' => DB::raw('fokusplan_bereiche.name')]);

        Schema::table('fokusplan_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bereich_id');
        });
    }
};
