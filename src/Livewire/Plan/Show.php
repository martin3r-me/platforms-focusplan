<?php

namespace Platform\Fokusplan\Livewire\Plan;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Fokusplan\Models\FokusplanPlan;
use Platform\Fokusplan\Models\FokusplanStep;
use Platform\Fokusplan\Services\FokusplanPhaseService;
use Platform\Fokusplan\Services\FokusplanStepService;

class Show extends Component
{
    public FokusplanPlan $plan;

    // Plan-Header bearbeiten
    public bool $showPlanModal = false;
    public string $planTitle = '';
    public string $planFachbereich = '';
    public string $planResponsible = '';
    public ?int $planYear = null;

    // Phase anlegen / bearbeiten
    public bool $showPhaseModal = false;
    public ?int $editingPhaseId = null;
    public string $phaseTitle = '';
    public string $phaseDescription = '';

    // Step anlegen / bearbeiten
    public bool $showStepModal = false;
    public ?int $editingStepId = null;
    public ?int $stepPhaseId = null;
    public string $stepGoal = '';
    public string $stepTitle = '';
    public string $stepDetails = '';
    public string $stepLead = '';
    /** @var array<int, string> */
    public array $stepSupporters = [];
    /** @var array<int, string> */
    public array $stepResources = [];
    public string $stepExternalProjectRef = '';
    public ?int $stepNewDependencyId = null;
    public string $stepKennzahl = '';
    public string $stepDeadline = '';
    public string $stepStatus = FokusplanStep::STATUS_OPEN;
    public string $stepStatusNote = '';

    // Filter
    public ?string $personFilter = null;

    public function mount(FokusplanPlan $plan)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if ($plan->team_id !== $team->id) {
            abort(403);
        }

        $this->plan = $plan;
    }

    // ---- Plan-Header ----

    public function openPlanModal()
    {
        $this->planTitle = $this->plan->title;
        $this->planFachbereich = $this->plan->fachbereich ?? '';
        $this->planResponsible = $this->plan->responsible ?? '';
        $this->planYear = $this->plan->year;
        $this->showPlanModal = true;
    }

    public function savePlan()
    {
        $title = trim($this->planTitle);
        if ($title === '') {
            return;
        }

        $this->plan->update([
            'title' => $title,
            'fachbereich' => trim($this->planFachbereich) ?: null,
            'responsible' => trim($this->planResponsible) ?: null,
            'year' => $this->planYear ?: null,
        ]);

        $this->showPlanModal = false;
        $this->dispatch('updateSidebar');
    }

    // ---- Phasen ----

    public function addPhase()
    {
        $this->editingPhaseId = null;
        $this->phaseTitle = '';
        $this->phaseDescription = '';
        $this->showPhaseModal = true;
    }

    public function editPhase(int $phaseId)
    {
        $phase = $this->plan->phases()->findOrFail($phaseId);
        $this->editingPhaseId = $phase->id;
        $this->phaseTitle = $phase->title;
        $this->phaseDescription = $phase->description ?? '';
        $this->showPhaseModal = true;
    }

    public function savePhase()
    {
        $title = trim($this->phaseTitle);
        if ($title === '') {
            return;
        }

        $service = new FokusplanPhaseService();
        $data = ['title' => $title, 'description' => trim($this->phaseDescription) ?: null];

        if ($this->editingPhaseId) {
            $phase = $this->plan->phases()->findOrFail($this->editingPhaseId);
            $service->updatePhase($phase, $data);
        } else {
            $data['created_by_user_id'] = Auth::id();
            $service->createPhase($this->plan, $data);
        }

        $this->showPhaseModal = false;
    }

    public function deletePhase(int $phaseId)
    {
        $phase = $this->plan->phases()->findOrFail($phaseId);
        (new FokusplanPhaseService())->deletePhase($phase);
    }

    // ---- Steps ----

    public function addStep(?int $phaseId = null)
    {
        $this->resetStepForm();
        $this->stepPhaseId = $phaseId;
        $this->showStepModal = true;
    }

    public function editStep(int $stepId)
    {
        $step = $this->plan->steps()->findOrFail($stepId);

        $this->editingStepId = $step->id;
        $this->stepPhaseId = $step->fokusplan_phase_id;
        $this->stepGoal = $step->goal ?? '';
        $this->stepTitle = $step->title;
        $this->stepDetails = $step->details ?? '';
        $this->stepLead = $step->lead ?? '';
        $this->stepSupporters = $step->supporters ?? [];
        $this->stepResources = $step->resources ?? [];
        $this->stepExternalProjectRef = $step->external_project_ref ?? '';
        $this->stepKennzahl = $step->kennzahl ?? '';
        $this->stepDeadline = $step->deadline?->format('Y-m-d') ?? '';
        $this->stepStatus = $step->status;
        $this->stepStatusNote = $step->status_note ?? '';
        $this->showStepModal = true;
    }

    public function saveStep()
    {
        $title = trim($this->stepTitle);
        if ($title === '') {
            return;
        }

        // Phase validieren (muss zum Plan gehören)
        $phaseId = null;
        if ($this->stepPhaseId) {
            $phase = $this->plan->phases()->find($this->stepPhaseId);
            $phaseId = $phase?->id;
        }

        $service = new FokusplanStepService();

        $data = [
            'fokusplan_phase_id' => $phaseId,
            'goal' => trim($this->stepGoal) ?: null,
            'title' => $title,
            'details' => trim($this->stepDetails) ?: null,
            'lead' => trim($this->stepLead) ?: null,
            'supporters' => FokusplanStep::normalizeSupporters($this->stepSupporters),
            'resources' => FokusplanStep::normalizeResources($this->stepResources),
            'external_project_ref' => trim($this->stepExternalProjectRef) ?: null,
            'kennzahl' => trim($this->stepKennzahl) ?: null,
            'deadline' => trim($this->stepDeadline) ?: null,
            'status' => in_array($this->stepStatus, array_keys(FokusplanStep::STATUSES), true)
                ? $this->stepStatus
                : FokusplanStep::STATUS_OPEN,
            'status_note' => trim($this->stepStatusNote) ?: null,
        ];

        if ($this->editingStepId) {
            $step = $this->plan->steps()->findOrFail($this->editingStepId);
            $service->updateStep($step, $data);
        } else {
            $data['created_by_user_id'] = Auth::id();
            $service->createStep($this->plan, $data);
        }

        $this->showStepModal = false;
        $this->resetStepForm();
    }

    public function addSupporter()
    {
        $this->stepSupporters[] = '';
    }

    public function removeSupporter(int $index)
    {
        unset($this->stepSupporters[$index]);
        $this->stepSupporters = array_values($this->stepSupporters);
    }

    public function addResource()
    {
        $this->stepResources[] = '';
    }

    public function removeResource(int $index)
    {
        unset($this->stepResources[$index]);
        $this->stepResources = array_values($this->stepResources);
    }

    public function addStepDependency()
    {
        $this->resetErrorBag('stepNewDependencyId');

        if (!$this->editingStepId || !$this->stepNewDependencyId) {
            return;
        }

        $step = $this->plan->steps()->findOrFail($this->editingStepId);
        $dependsOn = FokusplanStep::whereHas('plan', fn ($q) => $q->where('team_id', $this->plan->team_id))
            ->findOrFail($this->stepNewDependencyId);

        try {
            (new FokusplanStepService())->addDependency($step, $dependsOn);
            $this->stepNewDependencyId = null;
        } catch (\DomainException $e) {
            $this->addError('stepNewDependencyId', $e->getMessage());
        }
    }

    public function removeStepDependency(int $dependsOnStepId)
    {
        if (!$this->editingStepId) {
            return;
        }

        $step = $this->plan->steps()->findOrFail($this->editingStepId);
        $dependsOn = FokusplanStep::find($dependsOnStepId);
        if (!$dependsOn) {
            return;
        }

        (new FokusplanStepService())->removeDependency($step, $dependsOn);
    }

    public function setStatus(int $stepId, string $status)
    {
        if (!in_array($status, array_keys(FokusplanStep::STATUSES), true)) {
            return;
        }

        $step = $this->plan->steps()->findOrFail($stepId);
        $step->update(['status' => $status]);
    }

    public function deleteStep(int $stepId)
    {
        $step = $this->plan->steps()->findOrFail($stepId);
        $step->delete();
    }

    protected function resetStepForm(): void
    {
        $this->editingStepId = null;
        $this->stepPhaseId = null;
        $this->stepGoal = '';
        $this->stepTitle = '';
        $this->stepDetails = '';
        $this->stepLead = '';
        $this->stepSupporters = [];
        $this->stepResources = [];
        $this->stepExternalProjectRef = '';
        $this->stepNewDependencyId = null;
        $this->stepKennzahl = '';
        $this->stepDeadline = '';
        $this->stepStatus = FokusplanStep::STATUS_OPEN;
        $this->stepStatusNote = '';
    }

    public function render()
    {
        $phases = $this->plan->phases()->with('steps')->orderBy('position')->get();
        $looseSteps = $this->plan->steps()->whereNull('fokusplan_phase_id')->orderBy('position')->get();
        $allSteps = $this->plan->steps()->get();

        $teamMembers = $this->plan->team?->users()->pluck('name')->filter()->sort()->values() ?? collect();
        $personOptions = $allSteps
            ->flatMap(fn ($step) => array_merge([$step->lead], $step->supporters ?? []))
            ->filter()
            ->merge($teamMembers)
            ->unique()
            ->sort()
            ->values();

        $personFilter = trim((string) $this->personFilter);
        if ($personFilter !== '') {
            $phases->each(function ($phase) use ($personFilter) {
                $phase->setRelation('steps', $phase->steps->filter(fn ($step) => $step->involvesPerson($personFilter))->values());
            });
            $looseSteps = $looseSteps->filter(fn ($step) => $step->involvesPerson($personFilter))->values();
        }

        $currentDependencies = collect();
        $dependencyOptions = collect();
        if ($this->editingStepId) {
            $editingStep = FokusplanStep::find($this->editingStepId);
            if ($editingStep) {
                $currentDependencies = $editingStep->dependsOn()->with('plan')->get();
                $excludeIds = $currentDependencies->pluck('id')->push($editingStep->id);

                $dependencyOptions = FokusplanStep::whereHas('plan', fn ($q) => $q->where('team_id', $this->plan->team_id))
                    ->whereNotIn('id', $excludeIds)
                    ->with('plan')
                    ->get()
                    ->map(fn (FokusplanStep $s) => [
                        'id' => $s->id,
                        'label' => trim(($s->plan->fachbereich ?: $s->plan->title) . ' · ' . $s->title),
                    ])
                    ->sortBy('label')
                    ->values();
            }
        }

        return view('fokusplan::livewire.plan.show', [
            'phases' => $phases,
            'looseSteps' => $looseSteps,
            'statuses' => FokusplanStep::STATUSES,
            'teamMembers' => $teamMembers,
            'personOptions' => $personOptions,
            'totalSteps' => $allSteps->count(),
            'doneSteps' => $allSteps->where('status', FokusplanStep::STATUS_DONE)->count(),
            'blockedSteps' => $allSteps->where('status', FokusplanStep::STATUS_BLOCKED)->count(),
            'currentDependencies' => $currentDependencies,
            'dependencyOptions' => $dependencyOptions,
        ])->layout('platform::layouts.app');
    }
}
