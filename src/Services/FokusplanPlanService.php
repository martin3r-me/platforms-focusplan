<?php

namespace Platform\Fokusplan\Services;

use Platform\Fokusplan\Models\FokusplanPlan;

class FokusplanPlanService
{
    public function createPlan(array $data): FokusplanPlan
    {
        if (!isset($data['position'])) {
            $maxPosition = FokusplanPlan::where('team_id', $data['team_id'])->max('position');
            $data['position'] = ((int) $maxPosition) + 1;
        }

        return FokusplanPlan::create($data);
    }

    public function updatePlan(FokusplanPlan $plan, array $data): FokusplanPlan
    {
        $plan->update($data);
        return $plan->fresh();
    }

    public function deletePlan(FokusplanPlan $plan): void
    {
        $plan->delete();
    }
}
