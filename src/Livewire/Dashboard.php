<?php

namespace Platform\Fokusplan\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Fokusplan\Models\FokusplanPlan;
use Platform\Fokusplan\Models\FokusplanStep;

class Dashboard extends Component
{
    public function createPlan()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return;
        }

        $plan = FokusplanPlan::create([
            'title' => 'Fokusplan ' . now()->year,
            'year' => now()->year,
            'team_id' => $team->id,
            'created_by_user_id' => $user->id,
        ]);

        $this->dispatch('updateSidebar');
        return $this->redirect(route('fokusplan.plans.show', $plan), navigate: true);
    }

    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $plans = FokusplanPlan::where('team_id', $team->id)
            ->withCount('steps')
            ->orderByDesc('year')
            ->orderBy('position')
            ->get();

        $totalPlans = $plans->count();
        $totalSteps = FokusplanStep::whereHas('plan', fn ($q) => $q->where('team_id', $team->id))->count();
        $openSteps = FokusplanStep::whereHas('plan', fn ($q) => $q->where('team_id', $team->id))
            ->where('status', '!=', FokusplanStep::STATUS_DONE)
            ->count();
        $blockedSteps = FokusplanStep::whereHas('plan', fn ($q) => $q->where('team_id', $team->id))
            ->where('status', FokusplanStep::STATUS_BLOCKED)
            ->count();

        return view('fokusplan::livewire.dashboard', [
            'plans' => $plans,
            'totalPlans' => $totalPlans,
            'totalSteps' => $totalSteps,
            'openSteps' => $openSteps,
            'blockedSteps' => $blockedSteps,
        ])->layout('platform::layouts.app');
    }
}
