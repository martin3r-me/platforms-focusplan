<?php

namespace Platform\Fokusplan\Services;

use Platform\Fokusplan\Models\FokusplanGoal;
use Platform\Fokusplan\Models\FokusplanPhase;

class FokusplanGoalService
{
    public function createGoal(FokusplanPhase $phase, array $data): FokusplanGoal
    {
        if (!isset($data['position'])) {
            $data['position'] = ((int) FokusplanGoal::where('fokusplan_phase_id', $phase->id)->max('position')) + 1;
        }

        $data['fokusplan_plan_id'] = $phase->fokusplan_plan_id;
        $data['fokusplan_phase_id'] = $phase->id;

        return FokusplanGoal::create($data);
    }

    public function updateGoal(FokusplanGoal $goal, array $data): FokusplanGoal
    {
        $goal->update($data);
        return $goal->fresh();
    }

    public function deleteGoal(FokusplanGoal $goal): void
    {
        $goal->delete();
    }
}
