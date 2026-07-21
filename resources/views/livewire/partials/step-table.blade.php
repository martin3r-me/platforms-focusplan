{{-- Aktionsplan-Tabelle für eine Step-Sammlung.
     Erwartet: $steps (Collection), $statuses (array), $phaseId (int|null) --}}
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
                    <x-ui-table-cell compact="true"><span class="text-sm">{{ $step->lead ?: '–' }}</span></x-ui-table-cell>
                    <x-ui-table-cell compact="true"><span class="text-sm">{{ $step->kennzahl ?: '–' }}</span></x-ui-table-cell>
                    <x-ui-table-cell compact="true"><span class="text-sm">{{ $step->deadline ?: '–' }}</span></x-ui-table-cell>
                    <x-ui-table-cell compact="true">
                        @include('fokusplan::livewire.partials.step-status-select', ['step' => $step, 'statuses' => $statuses])
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
    <div class="py-6 text-center">
        <p class="text-sm text-[var(--ui-muted)] mb-3">Noch keine Steps.</p>
        <x-ui-button variant="secondary-outline" size="sm" wire:click="addStep({{ $phaseId ?? 'null' }})">
            <span class="flex items-center gap-2">@svg('heroicon-o-plus', 'w-4 h-4')<span>Step hinzufügen</span></span>
        </x-ui-button>
    </div>
@endif
