<?php

namespace Platform\Fokusplan\Livewire\Plan;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Fokusplan\Models\FokusplanPlan;
use Platform\Fokusplan\Models\FokusplanStep;
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

    // Step bearbeiten / anlegen
    public bool $showStepModal = false;
    public ?int $editingStepId = null;
    public string $stepTitle = '';
    public string $stepDetails = '';
    public string $stepLead = '';
    public string $stepKennzahl = '';
    public string $stepDeadline = '';
    public string $stepStatus = FokusplanStep::STATUS_OPEN;

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

    // ---- Steps ----

    public function addStep()
    {
        $this->resetStepForm();
        $this->editingStepId = null;
        $this->showStepModal = true;
    }

    public function editStep(int $stepId)
    {
        $step = $this->plan->steps()->findOrFail($stepId);

        $this->editingStepId = $step->id;
        $this->stepTitle = $step->title;
        $this->stepDetails = $step->details ?? '';
        $this->stepLead = $step->lead ?? '';
        $this->stepKennzahl = $step->kennzahl ?? '';
        $this->stepDeadline = $step->deadline ?? '';
        $this->stepStatus = $step->status;
        $this->showStepModal = true;
    }

    public function saveStep()
    {
        $title = trim($this->stepTitle);
        if ($title === '') {
            return;
        }

        $service = new FokusplanStepService();

        $data = [
            'title' => $title,
            'details' => trim($this->stepDetails) ?: null,
            'lead' => trim($this->stepLead) ?: null,
            'kennzahl' => trim($this->stepKennzahl) ?: null,
            'deadline' => trim($this->stepDeadline) ?: null,
            'status' => in_array($this->stepStatus, array_keys(FokusplanStep::STATUSES), true)
                ? $this->stepStatus
                : FokusplanStep::STATUS_OPEN,
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
        $this->stepTitle = '';
        $this->stepDetails = '';
        $this->stepLead = '';
        $this->stepKennzahl = '';
        $this->stepDeadline = '';
        $this->stepStatus = FokusplanStep::STATUS_OPEN;
    }

    public function render()
    {
        $steps = $this->plan->steps()->orderBy('position')->get();

        return view('fokusplan::livewire.plan.show', [
            'steps' => $steps,
            'statuses' => FokusplanStep::STATUSES,
        ])->layout('platform::layouts.app');
    }
}
