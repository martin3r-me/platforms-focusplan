<x-ui-page>
    {{-- Navbar --}}
    <x-slot name="navbar">
        <x-ui-page-navbar title="Fokusplan" icon="heroicon-o-flag" />
    </x-slot>

    {{-- Actionbar --}}
    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Fokusplan', 'icon' => 'flag'],
        ]">
            <x-ui-button variant="primary" size="sm" wire:click="createPlan">
                <span class="flex items-center gap-1.5">
                    @svg('heroicon-o-plus', 'w-4 h-4')
                    <span>Neuer Fokusplan</span>
                </span>
            </x-ui-button>
        </x-ui-page-actionbar>
    </x-slot>

    {{-- Hauptinhalt --}}
    <x-ui-page-container>
        <div class="space-y-8">
            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <x-ui-dashboard-tile title="Fokuspläne" :count="$totalPlans" subtitle="Gesamt" icon="flag" variant="secondary" size="lg" />
                <x-ui-dashboard-tile title="Steps" :count="$totalSteps" subtitle="Gesamt" icon="list-bullet" variant="secondary" size="lg" />
                <x-ui-dashboard-tile title="Offen" :count="$openSteps" subtitle="Noch nicht erledigt" icon="clock" variant="warning" size="lg" />
                <x-ui-dashboard-tile title="Blockiert" :count="$blockedSteps" subtitle="Brauchen eine Entscheidung" icon="exclamation-triangle" variant="danger" size="lg" />
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

    {{-- Linke Sidebar --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Übersicht" width="w-80" :defaultOpen="true">
            <div class="p-5 space-y-6">
                <div>
                    <h3 class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)] mb-3">Statistiken</h3>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-3 bg-[var(--ui-muted-5)] rounded-lg border border-[var(--ui-border)]/40">
                            <span class="text-xs text-[var(--ui-muted)]">Fokuspläne</span>
                            <span class="text-sm font-bold text-[var(--ui-secondary)]">{{ $totalPlans }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-[var(--ui-muted-5)] rounded-lg border border-[var(--ui-border)]/40">
                            <span class="text-xs text-[var(--ui-muted)]">Steps gesamt</span>
                            <span class="text-sm font-bold text-[var(--ui-secondary)]">{{ $totalSteps }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-[var(--ui-muted-5)] rounded-lg border border-[var(--ui-border)]/40">
                            <span class="text-xs text-[var(--ui-muted)]">Offen</span>
                            <span class="text-sm font-bold text-[var(--ui-secondary)]">{{ $openSteps }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    {{-- Rechte Sidebar (Aktivitäten) --}}
    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-5 space-y-3">
                <h3 class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)]">Zuletzt</h3>
                @forelse($plans->take(6) as $plan)
                    <a href="{{ route('fokusplan.plans.show', $plan) }}" wire:navigate
                       class="flex items-center gap-2 text-sm text-[var(--ui-secondary)] hover:text-[var(--ui-primary)] transition-colors truncate">
                        @svg('heroicon-o-flag', 'w-3.5 h-3.5 text-[var(--ui-muted)] flex-shrink-0')
                        <span class="truncate">{{ $plan->title }}</span>
                    </a>
                @empty
                    <div class="text-sm text-[var(--ui-muted)]">Keine Aktivitäten.</div>
                @endforelse
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
