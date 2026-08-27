<?php

namespace Platform\Fokusplan\Livewire\Orientation;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Fokusplan\Services\FokusplanOrientationService;

class Index extends Component
{
    public function render()
    {
        $team = Auth::user()?->currentTeam;

        $overview = $team
            ? app(FokusplanOrientationService::class)->buildOverview($team->id)
            : ['groups' => collect(), 'unassigned' => collect(), 'totalPotential' => []];

        return view('fokusplan::livewire.orientation.index', $overview)
            ->layout('platform::layouts.app');
    }
}
