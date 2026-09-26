<?php

namespace Platform\Fokusplan\Services;

use Illuminate\Support\Collection;
use Platform\Fokusplan\Models\FokusplanBereich;
use Platform\Fokusplan\Models\FokusplanGoal;
use Platform\Fokusplan\Models\FokusplanStep;

class FokusplanManagementService
{
    /**
     * Baut die Management-Übersicht (Issue #825): Sidebar "Alle Bereiche" +
     * je Bereich mit Zielanzahl, dazu die Tabellenzeilen (ein Fokusziel = eine
     * Zeile) für die aktuelle Auswahl sowie die Potenzial-Summe je Bereich und
     * gesamt. Ein Ziel gehört über seinen Plan zu genau einem Bereich, es gibt
     * also — anders als bei den Kategorien der Ausrichtungsseite (#831) —
     * keine n:m-Dopplung aufzulösen.
     *
     * @return array{
     *   bereiche: Collection<int, array{bereich: FokusplanBereich, goalCount: int, potentialEuro: float}>,
     *   totalGoalCount: int,
     *   totalPotentialEuro: float,
     *   selectedBereichId: ?int,
     *   selectedBereichName: ?string,
     *   rows: Collection<int, array>,
     * }
     */
    public function buildOverview(int $teamId, ?int $bereichId = null): array
    {
        $bereiche = FokusplanBereich::where('team_id', $teamId)
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        $goals = FokusplanGoal::whereHas('plan', fn ($q) => $q->where('team_id', $teamId))
            ->with(['plan.bereich', 'steps'])
            ->get();

        $bereicheOverview = $bereiche->map(function (FokusplanBereich $bereich) use ($goals) {
            $goalsInBereich = $goals->filter(
                fn (FokusplanGoal $goal) => $goal->plan?->bereich_id === $bereich->id
            );

            return [
                'bereich' => $bereich,
                'goalCount' => $goalsInBereich->count(),
                'potentialEuro' => $this->sumPotentialEuro($goalsInBereich),
            ];
        })->values();

        $selectedBereich = $bereichId ? $bereiche->firstWhere('id', $bereichId) : null;

        $rowGoals = $selectedBereich
            ? $goals->filter(fn (FokusplanGoal $goal) => $goal->plan?->bereich_id === $selectedBereich->id)
            : $goals;

        $rows = $rowGoals
            ->sortBy(fn (FokusplanGoal $goal) => $goal->bereichLabel() . '|' . $goal->title)
            ->map(fn (FokusplanGoal $goal) => $this->mapRow($goal))
            ->values();

        return [
            'bereiche' => $bereicheOverview,
            'totalGoalCount' => $goals->count(),
            'totalPotentialEuro' => $this->sumPotentialEuro($goals),
            'selectedBereichId' => $selectedBereich?->id,
            'selectedBereichName' => $selectedBereich?->name,
            'rows' => $rows,
        ];
    }

    /**
     * @return array{goal: FokusplanGoal, bereichLabel: string, deadline: ?\Illuminate\Support\Carbon, potentialEuro: float, ampel: array{key: string, label: string}}
     */
    private function mapRow(FokusplanGoal $goal): array
    {
        return [
            'goal' => $goal,
            'bereichLabel' => $goal->bereichLabel(),
            'deadline' => $goal->nextDeadline(),
            'potentialEuro' => $goal->potentialByUnit()[FokusplanStep::UNIT_EURO] ?? 0.0,
            'ampel' => $goal->statusAmpel(),
        ];
    }

    /**
     * @param Collection<int, FokusplanGoal> $goals
     */
    private function sumPotentialEuro(Collection $goals): float
    {
        return (float) $goals->sum(
            fn (FokusplanGoal $goal) => $goal->potentialByUnit()[FokusplanStep::UNIT_EURO] ?? 0.0
        );
    }
}
