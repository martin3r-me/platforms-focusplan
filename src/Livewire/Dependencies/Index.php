<?php

namespace Platform\Fokusplan\Livewire\Dependencies;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Fokusplan\Models\FokusplanStep;

class Index extends Component
{
    public string $bereichFilter = '';

    protected function bereichLabel($plan): string
    {
        return trim($plan->fachbereich ?: $plan->title);
    }

    public function render()
    {
        $team = Auth::user()?->currentTeam;

        $steps = $team
            ? FokusplanStep::whereHas('plan', fn ($q) => $q->where('team_id', $team->id))
                ->with(['plan', 'goal', 'dependsOn.plan', 'dependsOn.goal'])
                ->get()
            : collect();

        // Nur Maßnahmen, die tatsächlich etwas über Startbarkeit aussagen
        // (Abhängigkeit, Ressource oder externe Projektreferenz).
        $relevantSteps = $steps->filter(function (FokusplanStep $step) {
            return $step->dependsOn->isNotEmpty()
                || !empty($step->resources)
                || !empty($step->external_project_ref);
        });

        $bereichOptions = $steps
            ->map(fn (FokusplanStep $step) => $this->bereichLabel($step->plan))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($this->bereichFilter !== '') {
            $relevantSteps = $relevantSteps->filter(
                fn (FokusplanStep $step) => $this->bereichLabel($step->plan) === $this->bereichFilter
            );
        }

        $grouped = $relevantSteps
            ->groupBy(fn (FokusplanStep $step) => $this->bereichLabel($step->plan))
            ->sortKeys()
            ->map(function ($stepsInBereich) {
                return $stepsInBereich
                    ->groupBy(fn (FokusplanStep $step) => $step->goal?->title ?? '')
                    ->sortKeys()
                    ->map(fn ($stepsInGoal) => $stepsInGoal->sortBy('title')->values());
            });

        return view('fokusplan::livewire.dependencies.index', [
            'grouped' => $grouped,
            'bereichOptions' => $bereichOptions,
            'totalRelevant' => $relevantSteps->count(),
        ])->layout('platform::layouts.app');
    }
}
