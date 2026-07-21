<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Fokuspläne" icon="heroicon-o-flag">
            <x-slot name="actions">
                <x-ui-button variant="primary" size="sm" wire:click="createPlan">
                    <span class="flex items-center gap-1.5">
                        @svg('heroicon-o-plus', 'w-4 h-4')
                        <span>Neuer Fokusplan</span>
                    </span>
                </x-ui-button>
            </x-slot>
        </x-ui-page-navbar>
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            <div class="max-w-sm">
                <x-ui-input-text wire:model.live.debounce.300ms="search" placeholder="Fokusplan suchen..." />
            </div>

            <x-ui-panel>
                @if($plans->isNotEmpty())
                    <x-ui-table compact="true">
                        <x-ui-table-header>
                            <x-ui-table-header-cell compact="true">Titel</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true">Fachbereich</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true">Verantwortlich</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true">Jahr</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true" align="right">Steps</x-ui-table-header-cell>
                        </x-ui-table-header>
                        <x-ui-table-body>
                            @foreach($plans as $plan)
                                <x-ui-table-row compact="true" clickable="true" :href="route('fokusplan.plans.show', $plan)">
                                    <x-ui-table-cell compact="true">
                                        <div class="font-medium">{{ $plan->title }}</div>
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true">
                                        <span class="text-sm">{{ $plan->fachbereich ?: '–' }}</span>
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true">
                                        <span class="text-sm">{{ $plan->responsible ?: '–' }}</span>
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true">
                                        <span class="text-sm">{{ $plan->year ?: '–' }}</span>
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true" align="right">
                                        <x-ui-badge variant="secondary" size="sm">{{ $plan->steps_count }}</x-ui-badge>
                                    </x-ui-table-cell>
                                </x-ui-table-row>
                            @endforeach
                        </x-ui-table-body>
                    </x-ui-table>
                @else
                    <div class="py-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[var(--ui-muted-5)] mb-4">
                            @svg('heroicon-o-flag', 'w-8 h-8 text-[var(--ui-muted)]')
                        </div>
                        <p class="text-sm text-[var(--ui-muted)] mb-4">Keine Fokuspläne gefunden.</p>
                        <x-ui-button variant="primary" size="sm" wire:click="createPlan">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-plus', 'w-4 h-4')
                                <span>Fokusplan erstellen</span>
                            </span>
                        </x-ui-button>
                    </div>
                @endif
            </x-ui-panel>
        </div>
    </x-ui-page-container>
</x-ui-page>
