<?php

namespace Platform\Fokusplan\Services;

use Platform\Fokusplan\Models\FokusplanPlan;
use Platform\Fokusplan\Models\FokusplanPhase;

class FokusplanPhaseService
{
    public function createPhase(FokusplanPlan $plan, array $data): FokusplanPhase
    {
        if (!isset($data['position'])) {
            $maxPosition = FokusplanPhase::where('fokusplan_plan_id', $plan->id)->max('position');
            $data['position'] = ((int) $maxPosition) + 1;
        }

        $data['fokusplan_plan_id'] = $plan->id;

        return FokusplanPhase::create($data);
    }

    public function updatePhase(FokusplanPhase $phase, array $data): FokusplanPhase
    {
        $phase->update($data);
        return $phase->fresh();
    }

    public function deletePhase(FokusplanPhase $phase): void
    {
        // Steps behalten (fokusplan_phase_id wird via nullOnDelete auf null gesetzt),
        // bleiben also dem Plan erhalten, nur ohne Phase.
        $phase->delete();
    }

    /**
     * @param array<int> $phaseIds Phase-IDs in gewünschter Reihenfolge
     */
    public function reorderPhases(int $planId, array $phaseIds): void
    {
        foreach ($phaseIds as $position => $phaseId) {
            FokusplanPhase::where('id', $phaseId)
                ->where('fokusplan_plan_id', $planId)
                ->update(['position' => $position + 1]);
        }
    }
}
