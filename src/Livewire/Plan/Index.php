<?php

namespace Platform\Fokusplan\Livewire\Plan;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Fokusplan\Models\FokusplanPlan;

class Index extends Component
{
    public string $search = '';

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

        $query = FokusplanPlan::where('team_id', $team->id)
            ->withCount('steps')
            ->orderByDesc('year')
            ->orderBy('position');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('fachbereich', 'like', "%{$this->search}%")
                    ->orWhere('responsible', 'like', "%{$this->search}%");
            });
        }

        $plans = $query->get();

        return view('fokusplan::livewire.plan.index', [
            'plans' => $plans,
        ])->layout('platform::layouts.app');
    }
}
