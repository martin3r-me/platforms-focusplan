<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Fokusplan" icon="heroicon-o-flag">
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
        <div class="space-y-8">
            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-ui-dashboard-tile
                    title="Fokuspläne"
                    :count="$totalPlans"
                    subtitle="Gesamt"
                    icon="flag"
                    variant="secondary"
                    size="lg"
                />
                <x-ui-dashboard-tile
                    title="Steps"
                    :count="$totalSteps"
                    subtitle="Gesamt"
                    icon="list-bullet"
                    variant="secondary"
                    size="lg"
                />
                <x-ui-dashboard-tile
                    title="Offen"
                    :count="$openSteps"
                    subtitle="Noch nicht erledigt"
                    icon="clock"
                    variant="warning"
                    size="lg"
                />
            </div>

            {{-- Pläne --}}
            <x-ui-panel title="Fokuspläne" subtitle="Deine Aktionspläne">
                @if($plans->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($plans as $plan)
                            <a href="{{ route('fokusplan.plans.show', $plan) }}" wire:navigate
                               class="block p-4 rounded-xl border border-[var(--ui-border)]/60 hover:border-[var(--ui-primary)]/40 hover:bg-[var(--ui-muted-5)] transition-colors">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        @svg('heroicon-o-flag', 'w-5 h-5 text-[var(--ui-muted)] flex-shrink-0')
                                        <span class="font-semibold text-[var(--ui-secondary)] truncate">{{ $plan->title }}</span>
                                    </div>
                                    @if($plan->year)
                                        <x-ui-badge variant="secondary" size="sm">{{ $plan->year }}</x-ui-badge>
                                    @endif
                                </div>
                                @if($plan->fachbereich)
                                    <div class="text-xs text-[var(--ui-muted)] truncate mb-1">{{ $plan->fachbereich }}</div>
                                @endif
                                <div class="text-xs text-[var(--ui-muted)]">{{ $plan->steps_count }} Steps</div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[var(--ui-muted-5)] mb-4">
                            @svg('heroicon-o-flag', 'w-8 h-8 text-[var(--ui-muted)]')
                        </div>
                        <p class="text-sm text-[var(--ui-muted)] mb-4">Noch keine Fokuspläne vorhanden.</p>
                        <x-ui-button variant="primary" size="sm" wire:click="createPlan">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-plus', 'w-4 h-4')
                                <span>Ersten Fokusplan erstellen</span>
                            </span>
                        </x-ui-button>
                    </div>
                @endif
            </x-ui-panel>
        </div>
    </x-ui-page-container>
</x-ui-page>
