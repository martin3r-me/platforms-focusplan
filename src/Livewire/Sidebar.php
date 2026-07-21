<?php

namespace Platform\Fokusplan\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Platform\Fokusplan\Models\FokusplanPlan;

class Sidebar extends Component
{
    public string $sidebarSearch = '';

    #[On('updateSidebar')]
    public function updateSidebar()
    {
        // Re-render triggers fresh data
    }

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
        $user = auth()->user();
        $teamId = $user?->currentTeam?->id ?? null;

        if (!$user || !$teamId) {
            return view('fokusplan::livewire.sidebar', [
                'plans' => collect(),
            ]);
        }

        $query = FokusplanPlan::where('team_id', $teamId)
            ->withCount('steps')
            ->orderByDesc('year')
            ->orderBy('position');

        if ($this->sidebarSearch !== '') {
            $search = mb_strtolower($this->sidebarSearch);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(fachbereich) like ?', ["%{$search}%"]);
            });
        }

        $plans = $query->get();

        return view('fokusplan::livewire.sidebar', [
            'plans' => $plans,
        ]);
    }
}
