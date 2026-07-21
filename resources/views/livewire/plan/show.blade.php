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
                    <span>Kopf bearbeiten</span>
                </span>
            </x-ui-button>
            <x-ui-button variant="primary" size="sm" wire:click="addStep">
                <span class="flex items-center gap-1.5">
                    @svg('heroicon-o-plus', 'w-3.5 h-3.5')
                    <span>Neuer Step</span>
                </span>
            </x-ui-button>
        </x-ui-page-actionbar>
    </x-slot>

    {{-- Hauptinhalt --}}
    <x-ui-page-container>
        <div class="space-y-6">
            {{-- Aktionsplan --}}
            <x-ui-panel title="Aktionsplan" subtitle="Steps · Details · Lead · Kennzahl · Deadline · Status">
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
                <div class="pt-2">
                    <x-ui-button variant="secondary-outline" size="sm" wire:click="openPlanModal" class="w-full">
                        <span class="flex items-center gap-2 justify-center">
                            @svg('heroicon-o-pencil', 'w-4 h-4')
                            <span>Kopf bearbeiten</span>
                        </span>
                    </x-ui-button>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

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
