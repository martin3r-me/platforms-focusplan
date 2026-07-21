<?php

namespace Platform\Fokusplan\Services;

use Platform\Fokusplan\Models\FokusplanPlan;
use Platform\Fokusplan\Models\FokusplanStep;

class FokusplanStepService
{
    public function createStep(FokusplanPlan $plan, array $data): FokusplanStep
    {
        if (!isset($data['position'])) {
            $maxPosition = FokusplanStep::where('fokusplan_plan_id', $plan->id)->max('position');
            $data['position'] = ((int) $maxPosition) + 1;
        }

        $data['fokusplan_plan_id'] = $plan->id;

        return FokusplanStep::create($data);
    }

    public function updateStep(FokusplanStep $step, array $data): FokusplanStep
    {
        $step->update($data);
        return $step->fresh();
    }

    public function deleteStep(FokusplanStep $step): void
    {
        $step->delete();
    }

    /**
     * Reihenfolge der Steps neu setzen.
     *
     * @param array<int> $stepIds Step-IDs in gewünschter Reihenfolge
     */
    public function reorderSteps(int $planId, array $stepIds): void
    {
        foreach ($stepIds as $position => $stepId) {
            FokusplanStep::where('id', $stepId)
                ->where('fokusplan_plan_id', $planId)
                ->update(['position' => $position + 1]);
        }
    }
}
