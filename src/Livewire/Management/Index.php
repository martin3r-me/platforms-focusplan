<?php

namespace Platform\Fokusplan\Livewire\Management;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Fokusplan\Services\FokusplanManagementService;

/**
 * Management-Übersicht (Issue #825): Sidebar "Alle Bereiche" + Bereiche mit
 * Zielanzahl, dazu eine Tabelle über die Fokusziele der aktuellen Auswahl.
 */
class Index extends Component
{
    public ?int $bereichId = null;

    public function selectBereich(?int $bereichId = null): void
    {
        $this->bereichId = $bereichId;
    }

    public function render()
    {
        $team = Auth::user()?->currentTeam;

        $overview = $team
            ? app(FokusplanManagementService::class)->buildOverview($team->id, $this->bereichId)
            : [
                'bereiche' => collect(),
                'totalGoalCount' => 0,
                'totalPotentialEuro' => 0.0,
                'selectedBereichId' => null,
                'selectedBereichName' => null,
                'rows' => collect(),
            ];

        return view('fokusplan::livewire.management.index', $overview)
            ->layout('platform::layouts.app');
    }
}
