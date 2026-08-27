<x-ui-page>
    {{-- Navbar --}}
    <x-slot name="navbar">
        <x-ui-page-navbar title="Fokusplan" icon="heroicon-o-flag" />
    </x-slot>

    {{-- Actionbar --}}
    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Fokusplan', 'icon' => 'flag', 'href' => route('fokusplan.dashboard')],
            ['label' => $plan->title],
        ]">
            <x-ui-button variant="secondary-outline" size="sm" wire:click="openPlanModal">
                <span class="flex items-center gap-1.5">
                    @svg('heroicon-o-pencil', 'w-3.5 h-3.5')
                    <span>Kopf</span>
                </span>
            </x-ui-button>
            <x-ui-button variant="primary" size="sm" wire:click="addPhase">
                <span class="flex items-center gap-1.5">
                    @svg('heroicon-o-plus', 'w-3.5 h-3.5')
                    <span>Neue Phase</span>
                </span>
            </x-ui-button>
        </x-ui-page-actionbar>
    </x-slot>

    {{-- Hauptinhalt --}}
    <x-ui-page-container>
        <div x-data="{ view: (window.localStorage.getItem('fokusplan_view') || 'sektionen') }"
             x-init="$watch('view', v => window.localStorage.setItem('fokusplan_view', v))"
             class="space-y-6">

            {{-- Fortschritt + Ansicht-Umschalter --}}
            <div class="flex items-center gap-4 flex-wrap">
                <div class="flex-1 min-w-[200px]">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium text-[var(--ui-muted)]">Fortschritt</span>
                        <span class="text-xs text-[var(--ui-muted)] tabular-nums">{{ $doneSteps }}/{{ $totalSteps }} erledigt</span>
                    </div>
                    <div class="h-2 rounded-full bg-[var(--ui-muted-5)] overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-[var(--ui-success)] to-emerald-400 transition-all"
                             style="width: {{ $totalSteps > 0 ? round($doneSteps / $totalSteps * 100) : 0 }}%"></div>
                    </div>
                </div>

                @if($blockedSteps > 0)
                    <x-ui-badge variant="danger" size="sm">
                        <span class="flex items-center gap-1">
                            @svg('heroicon-o-exclamation-triangle', 'w-3.5 h-3.5')
                            <span>{{ $blockedSteps }} blockiert</span>
                        </span>
                    </x-ui-badge>
                @endif

                <div class="w-48 flex-shrink-0">
                    <x-ui-input-select
                        name="personFilter"
                        :options="$personOptions->map(fn($p) => ['value' => $p, 'label' => $p])->values()->all()"
                        optionValue="value"
                        optionLabel="label"
                        wire:model.live="personFilter"
                        :nullable="true"
                        nullLabel="– Alle Personen –"
                    />
                </div>

                <div class="inline-flex bg-[var(--ui-muted-5)] border border-[var(--ui-border)]/60 rounded-lg p-0.5 gap-0.5 flex-shrink-0">
                    <button type="button" @click="view = 'sektionen'"
                        :class="view === 'sektionen' ? 'bg-[var(--ui-surface)] text-[var(--ui-secondary)] shadow-sm' : 'text-[var(--ui-muted)]'"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors flex items-center gap-1.5">
                        @svg('heroicon-o-bars-3', 'w-3.5 h-3.5')
                        <span>Sektionen</span>
                    </button>
                    <button type="button" @click="view = 'board'"
                        :class="view === 'board' ? 'bg-[var(--ui-surface)] text-[var(--ui-secondary)] shadow-sm' : 'text-[var(--ui-muted)]'"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors flex items-center gap-1.5">
                        @svg('heroicon-o-view-columns', 'w-3.5 h-3.5')
                        <span>Board</span>
                    </button>
                </div>
            </div>

            {{-- ===== Sektionen (Default) ===== --}}
            <div x-show="view === 'sektionen'" class="space-y-6">
                @forelse($phases as $phase)
                    @php
                        $phaseDone = $phase->steps->where('status', 'done')->count();
                        $phaseTotal = $phase->steps->count();
                    @endphp
                    <x-ui-panel>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2.5">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-[var(--ui-primary)]/10 text-[var(--ui-primary)] text-sm font-bold flex-shrink-0">{{ $loop->iteration }}</span>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-[15px] font-semibold text-[var(--ui-secondary)] truncate">{{ $phase->title }}</h3>
                                            <span class="text-xs text-[var(--ui-muted)] tabular-nums flex-shrink-0">{{ $phaseDone }}/{{ $phaseTotal }}</span>
                                        </div>
                                        <div class="h-1.5 w-28 rounded-full bg-[var(--ui-muted-5)] overflow-hidden mt-1.5">
                                            <div class="h-full rounded-full bg-[var(--ui-success)] transition-all" style="width: {{ $phaseTotal > 0 ? round($phaseDone / $phaseTotal * 100) : 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                @if($phase->description)
                                    <p class="text-sm text-[var(--ui-muted)] mt-2">{{ $phase->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <x-ui-button variant="secondary-outline" size="xs" wire:click="addGoal({{ $phase->id }})">
                                    <span class="flex items-center gap-1">@svg('heroicon-o-flag', 'w-3.5 h-3.5')<span>Ziel</span></span>
                                </x-ui-button>
                                <x-ui-button variant="secondary-outline" size="xs" wire:click="addStep({{ $phase->id }})">
                                    <span class="flex items-center gap-1">@svg('heroicon-o-plus', 'w-3.5 h-3.5')<span>Step</span></span>
                                </x-ui-button>
                                <x-ui-button variant="secondary-outline" size="xs" wire:click="editPhase({{ $phase->id }})">
                                    @svg('heroicon-o-pencil', 'w-3.5 h-3.5')
                                </x-ui-button>
                                <x-ui-button variant="danger-outline" size="xs"
                                    wire:click="deletePhase({{ $phase->id }})"
                                    wire:confirm="Phase löschen? Die Steps bleiben (ohne Phase) erhalten.">
                                    @svg('heroicon-o-trash', 'w-3.5 h-3.5')
                                </x-ui-button>
                            </div>
                        </div>

                        @if($phase->goals->isNotEmpty())
                            @php
                                $ampelVariant = ['done' => 'success', 'critical' => 'danger', 'warning' => 'warning', 'neutral' => 'secondary'];
                            @endphp
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach($phase->goals as $goal)
                                    @php $ampel = $goal->statusAmpel(); $progress = $goal->progress(); @endphp
                                    <button type="button" wire:click="editGoal({{ $goal->id }})"
                                        class="inline-flex items-center gap-2 pl-3 pr-2.5 py-1.5 rounded-lg border border-[var(--ui-border)]/60 bg-[var(--ui-muted-5)]/40 hover:bg-[var(--ui-muted-5)] transition-colors text-left">
                                        @svg('heroicon-o-flag', 'w-3.5 h-3.5 text-[var(--ui-primary)] flex-shrink-0')
                                        <span class="text-xs font-medium text-[var(--ui-secondary)] max-w-[12rem] truncate">{{ $goal->title }}</span>
                                        <span class="text-[11px] text-[var(--ui-muted)] tabular-nums">{{ $progress['done'] }}/{{ $progress['total'] }}</span>
                                        <x-ui-badge :variant="$ampelVariant[$ampel['key']]" size="sm">{{ $ampel['label'] }}</x-ui-badge>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @include('fokusplan::livewire.partials.step-table', ['steps' => $phase->steps, 'statuses' => $statuses, 'phaseId' => $phase->id])
                    </x-ui-panel>
                @empty
                    <div class="py-12 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-[var(--ui-muted-5)] mb-3">
                            @svg('heroicon-o-rectangle-stack', 'w-6 h-6 text-[var(--ui-muted)]')
                        </div>
                        <p class="text-sm text-[var(--ui-muted)] mb-4">Noch keine Phasen. Lege die erste Phase (z.B. „Phase 1") an.</p>
                        <x-ui-button variant="primary" size="sm" wire:click="addPhase">
                            <span class="flex items-center gap-2">@svg('heroicon-o-plus', 'w-4 h-4')<span>Erste Phase anlegen</span></span>
                        </x-ui-button>
                    </div>
                @endforelse

                {{-- Steps ohne Phase --}}
                @if($looseSteps->isNotEmpty())
                    <x-ui-panel>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <h3 class="text-base font-semibold text-[var(--ui-secondary)]">Ohne Phase</h3>
                                <p class="text-xs text-[var(--ui-muted)]">Steps, die (noch) keiner Phase zugeordnet sind</p>
                            </div>
                            <x-ui-button variant="secondary-outline" size="xs" wire:click="addStep">
                                <span class="flex items-center gap-1">@svg('heroicon-o-plus', 'w-3.5 h-3.5')<span>Step</span></span>
                            </x-ui-button>
                        </div>
                        @include('fokusplan::livewire.partials.step-table', ['steps' => $looseSteps, 'statuses' => $statuses, 'phaseId' => null])
                    </x-ui-panel>
                @endif
            </div>

            {{-- ===== Board (Umschalt-Ansicht) ===== --}}
            <div x-show="view === 'board'" style="display:none">
                @include('fokusplan::livewire.partials.board', ['phases' => $phases, 'looseSteps' => $looseSteps, 'statuses' => $statuses])
            </div>
        </div>
    </x-ui-page-container>

    {{-- Linke Sidebar: Kopf-Infos --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Details" width="w-80" :defaultOpen="true">
            <div class="p-5 space-y-4">
                <div>
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)] mb-1">Fachbereich</div>
                    <div class="text-sm text-[var(--ui-secondary)]">{{ $plan->fachbereich ?: '–' }}</div>
                </div>
                <div>
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)] mb-1">Verantwortlich</div>
                    <div class="text-sm text-[var(--ui-secondary)]">{{ $plan->responsible ?: '–' }}</div>
                </div>
                <div>
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)] mb-1">Jahr</div>
                    <div class="text-sm text-[var(--ui-secondary)]">{{ $plan->year ?: '–' }}</div>
                </div>
                <div class="pt-2 space-y-2">
                    <x-ui-button variant="secondary-outline" size="sm" wire:click="openPlanModal" class="w-full">
                        <span class="flex items-center gap-2 justify-center">@svg('heroicon-o-pencil', 'w-4 h-4')<span>Kopf bearbeiten</span></span>
                    </x-ui-button>
                    <x-ui-button variant="primary" size="sm" wire:click="addPhase" class="w-full">
                        <span class="flex items-center gap-2 justify-center">@svg('heroicon-o-plus', 'w-4 h-4')<span>Neue Phase</span></span>
                    </x-ui-button>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    {{-- Rechte Sidebar (Status-Übersicht) --}}
    <x-slot name="activity">
        <x-ui-page-sidebar title="Status" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-5 space-y-3">
                <h3 class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)]">Fortschritt</h3>
                @foreach($statuses as $value => $label)
                    <div class="flex items-center justify-between p-3 bg-[var(--ui-muted-5)] rounded-lg border border-[var(--ui-border)]/40">
                        <span class="text-xs text-[var(--ui-muted)]">{{ $label }}</span>
                        <span class="text-sm font-bold text-[var(--ui-secondary)]">{{ ($looseSteps->concat($phases->flatMap->steps))->where('status', $value)->count() }}</span>
                    </div>
                @endforeach
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    {{-- Plan-Header Modal --}}
    @if($showPlanModal)
        <x-ui-modal wire:model="showPlanModal" title="Fokusplan bearbeiten">
            <div class="space-y-4">
                <x-ui-input-text wire:model="planTitle" label="Titel" required />
                <x-ui-input-text wire:model="planFachbereich" label="Fachbereich" placeholder="z.B. Bankettprofi" />
                <x-ui-input-text wire:model="planResponsible" label="Verantwortlich" />
                <x-ui-input-text wire:model="planYear" type="number" label="Jahr" placeholder="2026" />
            </div>
            <x-slot name="footer">
                <x-ui-button variant="secondary-outline" wire:click="$set('showPlanModal', false)">Abbrechen</x-ui-button>
                <x-ui-button variant="primary" wire:click="savePlan">Speichern</x-ui-button>
            </x-slot>
        </x-ui-modal>
    @endif

    {{-- Phase Modal --}}
    @if($showPhaseModal)
        <x-ui-modal wire:model="showPhaseModal" :title="$editingPhaseId ? 'Phase bearbeiten' : 'Neue Phase'">
            <div class="space-y-4">
                <x-ui-input-text wire:model="phaseTitle" label="Titel" placeholder="z.B. Phase 1" required />
                <x-ui-input-textarea wire:model="phaseDescription" label="Beschreibung" rows="3" />
            </div>
            <x-slot name="footer">
                <x-ui-button variant="secondary-outline" wire:click="$set('showPhaseModal', false)">Abbrechen</x-ui-button>
                <x-ui-button variant="primary" wire:click="savePhase">Speichern</x-ui-button>
            </x-slot>
        </x-ui-modal>
    @endif

    {{-- Step Modal --}}
    @if($showStepModal)
        <x-ui-modal wire:model="showStepModal" :title="$editingStepId ? 'Step bearbeiten' : 'Neuer Step'">
            <div class="space-y-4">
                <x-ui-input-select
                    name="stepGoalId"
                    label="Ziel"
                    :options="$allGoals->map(fn($g) => ['value' => $g->id, 'label' => $g->phase->title . ' – ' . $g->title])->values()->all()"
                    optionValue="value"
                    optionLabel="label"
                    wire:model="stepGoalId"
                    :nullable="true"
                    nullLabel="– Kein Ziel verknüpft –"
                />
                <x-ui-input-text wire:model="stepGoal" label="Übergeordnetes Ziel (Freitext)" placeholder="optional, falls (noch) kein Ziel oben ausgewählt ist" />
                <x-ui-input-text wire:model="stepTitle" label="Steps / Maßnahme" required />
                <x-ui-input-textarea wire:model="stepDetails" label="Details" rows="3" placeholder="Ein Punkt pro Zeile …" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui-input-select
                        name="stepPhaseId"
                        label="Phase"
                        :options="$phases->map(fn($p) => ['value' => $p->id, 'label' => $p->title])->values()->all()"
                        optionValue="value"
                        optionLabel="label"
                        wire:model="stepPhaseId"
                        :nullable="true"
                        nullLabel="– Ohne Phase –"
                    />
                    <x-ui-input-select
                        name="stepStatus"
                        label="Status"
                        :options="collect($statuses)->map(fn($label, $value) => ['value' => $value, 'label' => $label])->values()->all()"
                        optionValue="value"
                        optionLabel="label"
                        wire:model="stepStatus"
                    />
                    <x-ui-input-text wire:model="stepLead" label="Verantwortlich" list="fokusplan-people" placeholder="Team-Mitglied oder Rolle, z.B. BHG.DIGITAL" />
                    <x-ui-input-text wire:model="stepKennzahl" label="Kennzahl" />
                    <x-ui-input-text wire:model="stepDeadline" type="date" label="Deadline" />
                </div>

                <datalist id="fokusplan-people">
                    @foreach($teamMembers as $teamMember)
                        <option value="{{ $teamMember }}"></option>
                    @endforeach
                </datalist>
                <div>
                    <label class="block text-xs font-medium text-[var(--ui-muted)] mb-1.5">Unterstützer</label>
                    <div class="space-y-2">
                        @foreach($stepSupporters as $i => $supporter)
                            <div wire:key="step-supporter-{{ $i }}" class="flex items-center gap-2">
                                <input type="text" list="fokusplan-people"
                                       wire:model="stepSupporters.{{ $i }}"
                                       placeholder="Team-Mitglied oder Rolle, z.B. HR"
                                       class="w-full text-sm rounded-md border-[var(--ui-border)] focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" />
                                <button type="button" wire:click="removeSupporter({{ $i }})"
                                        class="p-1.5 rounded-lg text-[var(--ui-muted)] hover:text-[var(--ui-danger)] hover:bg-[var(--ui-danger)]/10 flex-shrink-0">
                                    @svg('heroicon-o-x-mark', 'w-4 h-4')
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" wire:click="addSupporter"
                            class="mt-2 text-xs font-medium text-[var(--ui-primary)] hover:underline inline-flex items-center gap-1">
                        @svg('heroicon-o-plus', 'w-3.5 h-3.5')
                        <span>Unterstützer hinzufügen</span>
                    </button>
                </div>

                <x-ui-input-textarea wire:model="stepStatusNote" label="Status-Notiz" rows="2" placeholder="z.B. Hold 06/26, 60% erledigt …" />
            </div>
            <x-slot name="footer">
                <x-ui-button variant="secondary-outline" wire:click="$set('showStepModal', false)">Abbrechen</x-ui-button>
                <x-ui-button variant="primary" wire:click="saveStep">Speichern</x-ui-button>
            </x-slot>
        </x-ui-modal>
    @endif

    {{-- Ziel-Modal: Steuerungsblock (Issue #827) --}}
    @if($showGoalModal)
        @php
            $editingGoal = $editingGoalId ? $allGoals->firstWhere('id', $editingGoalId) : null;
            $ampelVariant = ['done' => 'success', 'critical' => 'danger', 'warning' => 'warning', 'neutral' => 'secondary'];
        @endphp
        <x-ui-modal wire:model="showGoalModal" :title="$editingGoalId ? 'Ziel bearbeiten' : 'Neues Ziel'">
            <div class="space-y-4">
                <x-ui-input-text wire:model="goalTitle" label="Titel" required />
                <x-ui-input-textarea wire:model="goalDescription" label="Beschreibung" rows="2" />

                {{-- Steuerung: zweispaltiges Kachel-Grid --}}
                <div>
                    <h4 class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)] mb-2">Steuerung</h4>
                    <div class="grid grid-cols-2 gap-3">
                        @if($editingGoal)
                            @php $ampel = $editingGoal->statusAmpel(); $progress = $editingGoal->progress(); @endphp
                            <div class="p-3 rounded-lg border border-[var(--ui-border)]/60 bg-[var(--ui-muted-5)]/40">
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)] mb-1.5">Status</div>
                                <x-ui-badge :variant="$ampelVariant[$ampel['key']]" size="sm">{{ $ampel['label'] }}</x-ui-badge>
                            </div>
                            <div class="p-3 rounded-lg border border-[var(--ui-border)]/60 bg-[var(--ui-muted-5)]/40">
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)] mb-1.5">Fortschritt</div>
                                <div class="text-sm font-semibold text-[var(--ui-secondary)] tabular-nums">{{ $progress['percent'] }}% <span class="text-xs font-normal text-[var(--ui-muted)]">({{ $progress['done'] }}/{{ $progress['total'] }} Maßnahmen)</span></div>
                            </div>
                        @endif

                        <div class="p-3 rounded-lg border border-[var(--ui-border)]/60">
                            <x-ui-input-text wire:model="goalResponsible" label="Verantwortlich" />
                        </div>
                        <div class="p-3 rounded-lg border border-[var(--ui-border)]/60">
                            <x-ui-input-text wire:model="goalKpi" label="KPI / Erfolgskriterium" />
                        </div>
                        <div class="p-3 rounded-lg border border-[var(--ui-border)]/60">
                            <x-ui-input-text wire:model="goalPotential" label="Potenzial (€)" placeholder="z.B. 50000" />
                        </div>
                        <div class="p-3 rounded-lg border border-[var(--ui-border)]/60">
                            <x-ui-input-text wire:model="goalImpact" label="Wirkung" placeholder="z.B. DB II, HK I, Umsatz, Kunde" />
                        </div>

                        {{-- Risiko/Blocker: volle Breite, Bernstein --}}
                        <div class="col-span-2 p-3 rounded-lg border border-[var(--ui-warning)]/40 bg-[var(--ui-warning)]/10">
                            <x-ui-input-textarea wire:model="goalRiskNote" label="Risiko / Blocker" rows="2" />
                        </div>

                        <div class="col-span-2 p-3 rounded-lg border border-[var(--ui-border)]/60">
                            <x-ui-input-textarea wire:model="goalDiagnosis" label="Diagnose" rows="2" placeholder="Warum steht die Ampel so?" />
                        </div>
                    </div>
                </div>
            </div>
            <x-slot name="footer">
                @if($editingGoalId)
                    <x-ui-button variant="danger-outline" wire:click="deleteGoal({{ $editingGoalId }})" wire:confirm="Dieses Ziel wirklich löschen? Verknüpfte Steps verlieren die Zuordnung.">Löschen</x-ui-button>
                @endif
                <x-ui-button variant="secondary-outline" wire:click="$set('showGoalModal', false)">Abbrechen</x-ui-button>
                <x-ui-button variant="primary" wire:click="saveGoal">Speichern</x-ui-button>
            </x-slot>
        </x-ui-modal>
    @endif
</x-ui-page>
