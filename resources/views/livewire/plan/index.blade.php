<x-ui-page>
    {{-- Navbar --}}
    <x-slot name="navbar">
        <x-ui-page-navbar title="Fokusplan" icon="heroicon-o-flag" />
    </x-slot>

    {{-- Actionbar --}}
    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Fokusplan', 'icon' => 'flag', 'href' => route('fokusplan.dashboard')],
            ['label' => 'Alle Fokuspläne'],
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

    {{-- Linke Sidebar --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Übersicht" width="w-80" :defaultOpen="true">
            <div class="p-5 space-y-4">
                <div class="flex items-center justify-between p-3 bg-[var(--ui-muted-5)] rounded-lg border border-[var(--ui-border)]/40">
                    <span class="text-xs text-[var(--ui-muted)]">Fokuspläne</span>
                    <span class="text-sm font-bold text-[var(--ui-secondary)]">{{ $plans->count() }}</span>
                </div>
                <x-ui-button variant="primary" size="sm" wire:click="createPlan" class="w-full">
                    <span class="flex items-center gap-2 justify-center">
                        @svg('heroicon-o-plus', 'w-4 h-4')
                        <span>Neuer Fokusplan</span>
                    </span>
                </x-ui-button>
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
