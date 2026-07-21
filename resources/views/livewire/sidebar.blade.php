<div x-data="{ collapsed: false }" class="h-full flex flex-col">
    {{-- Search --}}
    <div class="p-3 border-b border-[var(--ui-border)]/40">
        <div class="relative">
            @svg('heroicon-o-magnifying-glass', 'w-4 h-4 text-[var(--ui-muted)] absolute left-2.5 top-1/2 -translate-y-1/2')
            <input
                wire:model.live.debounce.300ms="sidebarSearch"
                type="text"
                placeholder="Suche..."
                class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg bg-[var(--ui-muted-5)] border border-[var(--ui-border)]/40 text-[var(--ui-secondary)] placeholder-[var(--ui-muted)] focus:outline-none focus:border-[var(--ui-primary)]/40"
            />
        </div>
    </div>

    <div class="flex-1 overflow-y-auto p-2 space-y-1">
        {{-- Dashboard --}}
        <a href="{{ route('fokusplan.dashboard') }}" wire:navigate
           class="flex items-center gap-2 px-3 py-2 text-xs font-medium rounded-lg text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)] transition-colors">
            @svg('heroicon-o-home', 'w-4 h-4 text-[var(--ui-muted)]')
            <span>Dashboard</span>
        </a>

        {{-- Alle Pläne --}}
        <a href="{{ route('fokusplan.plans.index') }}" wire:navigate
           class="flex items-center gap-2 px-3 py-2 text-xs font-medium rounded-lg text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)] transition-colors">
            @svg('heroicon-o-flag', 'w-4 h-4 text-[var(--ui-muted)]')
            <span>Alle Fokuspläne</span>
        </a>

        {{-- Quick Action --}}
        <div class="pt-2 pb-1">
            <button wire:click="createPlan"
                    class="flex items-center gap-2 px-3 py-1.5 text-xs rounded-lg text-[var(--ui-muted)] hover:text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)] transition-colors w-full">
                @svg('heroicon-o-plus', 'w-3.5 h-3.5')
                <span>Neuer Fokusplan</span>
            </button>
        </div>

        {{-- Pläne --}}
        <div class="pt-2">
            <div class="px-3 pb-1">
                <span class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)]">Fokuspläne</span>
            </div>
            @forelse($plans as $plan)
                <a href="{{ route('fokusplan.plans.show', $plan) }}" wire:navigate
                   class="flex items-center gap-2 px-3 py-1.5 text-xs rounded-lg text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)] transition-colors truncate">
                    @svg('heroicon-o-flag', 'w-3.5 h-3.5 text-[var(--ui-muted)] flex-shrink-0')
                    <span class="truncate">{{ $plan->title }}</span>
                    <span class="ml-auto text-[10px] text-[var(--ui-muted)]">{{ $plan->steps_count }}</span>
                </a>
            @empty
                <div class="px-3 py-2 text-xs text-[var(--ui-muted)]">Keine Pläne</div>
            @endforelse
        </div>
    </div>
</div>
