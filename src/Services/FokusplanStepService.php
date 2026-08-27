<?php

namespace Platform\Fokusplan\Services;

use Platform\Fokusplan\Models\FokusplanPlan;
use Platform\Fokusplan\Models\FokusplanStep;

class FokusplanStepService
{
    public function createStep(FokusplanPlan $plan, array $data): FokusplanStep
    {
        if (!isset($data['position'])) {
            $query = FokusplanStep::where('fokusplan_plan_id', $plan->id);
            // Position innerhalb der Phase (bzw. der phasenlosen Steps) hochzählen.
            if (array_key_exists('fokusplan_phase_id', $data) && $data['fokusplan_phase_id']) {
                $query->where('fokusplan_phase_id', $data['fokusplan_phase_id']);
            } else {
                $query->whereNull('fokusplan_phase_id');
            }
            $data['position'] = ((int) $query->max('position')) + 1;
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
     * Fügt eine "Wartet auf"-Abhängigkeit hinzu (Issue #828).
     *
     * @throws \DomainException bei Selbstreferenz oder Zyklus
     */
    public function addDependency(FokusplanStep $step, FokusplanStep $dependsOn): void
    {
        if ($step->id === $dependsOn->id) {
            throw new \DomainException('Eine Maßnahme kann nicht von sich selbst abhängen.');
        }

        if ($step->dependsOn()->where('depends_on_step_id', $dependsOn->id)->exists()) {
            return;
        }

        if ($dependsOn->dependsTransitivelyOn($step)) {
            throw new \DomainException('Diese Abhängigkeit würde einen Zyklus erzeugen.');
        }

        $step->dependsOn()->attach($dependsOn->id);
    }

    public function removeDependency(FokusplanStep $step, FokusplanStep $dependsOn): void
    {
        $step->dependsOn()->detach($dependsOn->id);
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
