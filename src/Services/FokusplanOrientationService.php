<?php

namespace Platform\Fokusplan\Services;

use Illuminate\Support\Collection;
use Platform\Fokusplan\Models\FokusplanCategory;
use Platform\Fokusplan\Models\FokusplanGoal;

class FokusplanOrientationService
{
    /**
     * Baut die Gruppierung für die Ausrichtungsseite (Issue #831): je Kategorie
     * die zugeordneten Ziele plus Kennzahlen, dazu die Gruppe "Ohne Ausrichtung"
     * und eine deduplizierte Gesamtsumme.
     *
     * @return array{groups: Collection<int, array>, unassigned: Collection<int, FokusplanGoal>, totalPotential: array<string, float>}
     */
    public function buildOverview(int $teamId): array
    {
        FokusplanCategory::ensureDefaultsForTeam($teamId);

        $categories = FokusplanCategory::where('team_id', $teamId)
            ->orderBy('position')
            ->get();

        $goals = FokusplanGoal::whereHas('plan', fn ($q) => $q->where('team_id', $teamId))
            ->with(['plan', 'steps', 'categories'])
            ->get();

        $groups = $categories->map(function (FokusplanCategory $category) use ($goals) {
            $goalsInCategory = $goals
                ->filter(fn (FokusplanGoal $goal) => $goal->categories->contains('id', $category->id))
                ->values();

            return [
                'category' => $category,
                'goals' => $goalsInCategory,
                'goalCount' => $goalsInCategory->count(),
                'bereichCount' => $goalsInCategory
                    ->map(fn (FokusplanGoal $goal) => $goal->bereichLabel())
                    ->filter()
                    ->unique()
                    ->count(),
                // Volles Potenzial je Ziel, das der Kategorie zugeordnet ist ("beteiligt
                // am Potenzial X") — bewusst nicht dedupliziert, siehe totalPotential.
                'potential' => $this->sumPotential($goalsInCategory),
            ];
        });

        $unassigned = $goals
            ->filter(fn (FokusplanGoal $goal) => $goal->categories->isEmpty())
            ->values();

        return [
            'groups' => $groups,
            'unassigned' => $unassigned,
            // Jedes Ziel zählt hier unabhängig von der Anzahl seiner Kategorien nur
            // einmal — löst die in Issue #831/AC4 geforderte Doppelzählung bei
            // n:m-Zuordnung für die Gesamtsumme.
            'totalPotential' => $this->sumPotential($goals),
        ];
    }

    /**
     * Summiert das Step-hochgerechnete Potenzial je Einheit über eine Menge Ziele.
     *
     * @param Collection<int, FokusplanGoal> $goals
     * @return array<string, float>
     */
    private function sumPotential(Collection $goals): array
    {
        $sums = [];

        foreach ($goals as $goal) {
            foreach ($goal->potentialByUnit() as $unit => $value) {
                $sums[$unit] = ($sums[$unit] ?? 0.0) + $value;
            }
        }

        return $sums;
    }
}
