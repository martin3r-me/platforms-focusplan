<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar :title="$plan->title" icon="heroicon-o-flag">
            <x-slot name="actions">
                <x-ui-button variant="secondary-outline" size="xs" wire:click="openPlanModal">
                    @svg('heroicon-o-pencil', 'w-3.5 h-3.5')
                </x-ui-button>
                <x-ui-button variant="primary" size="xs" wire:click="addStep">
                    <span class="flex items-center gap-1.5">
                        @svg('heroicon-o-plus', 'w-3.5 h-3.5')
                        <span>Neuer Step</span>
                    </span>
                </x-ui-button>
            </x-slot>
        </x-ui-page-navbar>
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            {{-- Kopf-Infos --}}
            <div class="flex flex-wrap items-center gap-4 text-xs text-[var(--ui-muted)]">
                @if($plan->fachbereich)
                    <span class="flex items-center gap-1">
                        @svg('heroicon-o-building-office-2', 'w-3.5 h-3.5')
                        <strong class="text-[var(--ui-secondary)]">Fachbereich:</strong> {{ $plan->fachbereich }}
                    </span>
                @endif
                @if($plan->responsible)
                    <span class="flex items-center gap-1">
                        @svg('heroicon-o-user', 'w-3.5 h-3.5')
                        <strong class="text-[var(--ui-secondary)]">Verantwortlich:</strong> {{ $plan->responsible }}
                    </span>
                @endif
                @if($plan->year)
                    <span class="flex items-center gap-1">
                        @svg('heroicon-o-calendar', 'w-3.5 h-3.5')
                        {{ $plan->year }}
                    </span>
                @endif
            </div>

            {{-- Aktionsplan --}}
            <x-ui-panel title="Aktionsplan" subtitle="Steps, Details, Lead, Kennzahl, Deadline, Status">
                @if($steps->isNotEmpty())
                    <x-ui-table compact="true">
                        <x-ui-table-header>
                            <x-ui-table-header-cell compact="true">Steps</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true">Details</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true">Lead</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true">Kennzahl</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true">Deadline</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true">Status</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true" align="right">Aktionen</x-ui-table-header-cell>
                        </x-ui-table-header>
                        <x-ui-table-body>
                            @foreach($steps as $step)
                                <x-ui-table-row compact="true">
                                    <x-ui-table-cell compact="true">
                                        <div class="font-medium max-w-[16rem]">{{ $step->title }}</div>
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true">
                                        @if($step->details)
                                            <div class="text-xs text-[var(--ui-muted)] whitespace-pre-line max-w-sm">{{ $step->details }}</div>
                                        @else
                                            <span class="text-xs text-[var(--ui-muted)]">–</span>
                                        @endif
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true">
                                        <span class="text-sm">{{ $step->lead ?: '–' }}</span>
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true">
                                        <span class="text-sm">{{ $step->kennzahl ?: '–' }}</span>
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true">
                                        <span class="text-sm">{{ $step->deadline ?: '–' }}</span>
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true">
                                        @php
                                            $statusVariant = match($step->status) {
                                                \Platform\Fokusplan\Models\FokusplanStep::STATUS_DONE => 'success',
                                                \Platform\Fokusplan\Models\FokusplanStep::STATUS_IN_PROGRESS => 'warning',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <select
                                            wire:change="setStatus({{ $step->id }}, $event.target.value)"
                                            class="text-xs rounded-lg bg-[var(--ui-muted-5)] border border-[var(--ui-border)]/40 px-2 py-1 text-[var(--ui-secondary)] focus:outline-none focus:border-[var(--ui-primary)]/40"
                                        >
                                            @foreach($statuses as $value => $label)
                                                <option value="{{ $value }}" @selected($step->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true" align="right">
                                        <div class="flex items-center justify-end gap-1">
                                            <x-ui-button variant="secondary-outline" size="xs" wire:click="editStep({{ $step->id }})">
                                                @svg('heroicon-o-pencil', 'w-3.5 h-3.5')
                                            </x-ui-button>
                                            <x-ui-button variant="danger-outline" size="xs"
                                                wire:click="deleteStep({{ $step->id }})"
                                                wire:confirm="Diesen Step wirklich löschen?">
                                                @svg('heroicon-o-trash', 'w-3.5 h-3.5')
                                            </x-ui-button>
                                        </div>
                                    </x-ui-table-cell>
                                </x-ui-table-row>
                            @endforeach
                        </x-ui-table-body>
                    </x-ui-table>
                @else
                    <div class="py-12 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-[var(--ui-muted-5)] mb-3">
                            @svg('heroicon-o-list-bullet', 'w-6 h-6 text-[var(--ui-muted)]')
                        </div>
                        <p class="text-sm text-[var(--ui-muted)] mb-4">Noch keine Steps in diesem Fokusplan.</p>
                        <x-ui-button variant="primary" size="sm" wire:click="addStep">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-plus', 'w-4 h-4')
                                <span>Ersten Step hinzufügen</span>
                            </span>
                        </x-ui-button>
                    </div>
                @endif
            </x-ui-panel>
        </div>
    </x-ui-page-container>

    {{-- Plan-Header Modal --}}
    @if($showPlanModal)
        <x-ui-modal wire:model="showPlanModal" title="Fokusplan bearbeiten">
            <div class="space-y-4">
                <x-ui-input-text wire:model="planTitle" label="Titel" required />
                <x-ui-input-text wire:model="planFachbereich" label="Fachbereich" placeholder="z.B. BANKETTPROFI PHASE 1" />
                <x-ui-input-text wire:model="planResponsible" label="Verantwortlich" />
                <x-ui-input-text wire:model="planYear" type="number" label="Jahr" placeholder="2026" />
            </div>
            <x-slot name="footer">
                <x-ui-button variant="secondary-outline" wire:click="$set('showPlanModal', false)">Abbrechen</x-ui-button>
                <x-ui-button variant="primary" wire:click="savePlan">Speichern</x-ui-button>
            </x-slot>
        </x-ui-modal>
    @endif

    {{-- Step Modal --}}
    @if($showStepModal)
        <x-ui-modal wire:model="showStepModal" :title="$editingStepId ? 'Step bearbeiten' : 'Neuer Step'">
            <div class="space-y-4">
                <x-ui-input-text wire:model="stepTitle" label="Steps (Titel)" required />
                <x-ui-input-textarea wire:model="stepDetails" label="Details" rows="4" placeholder="Ein Punkt pro Zeile …" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui-input-text wire:model="stepLead" label="Lead" placeholder="z.B. BHG.DIGITAL" />
                    <x-ui-input-text wire:model="stepKennzahl" label="Kennzahl" />
                    <x-ui-input-text wire:model="stepDeadline" label="Deadline" placeholder="z.B. Ende Q1" />
                    <x-ui-input-select
                        name="stepStatus"
                        label="Status"
                        :options="collect($statuses)->map(fn($label, $value) => ['value' => $value, 'label' => $label])->values()->all()"
                        optionValue="value"
                        optionLabel="label"
                        wire:model="stepStatus"
                    />
                </div>
            </div>
            <x-slot name="footer">
                <x-ui-button variant="secondary-outline" wire:click="$set('showStepModal', false)">Abbrechen</x-ui-button>
                <x-ui-button variant="primary" wire:click="saveStep">Speichern</x-ui-button>
            </x-slot>
        </x-ui-modal>
    @endif
</x-ui-page>
