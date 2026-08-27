{{--
    Modul-Sidebar für Fokusplan.
    WICHTIG: KEIN eigenes x-data — der `collapsed`-Scope kommt vom Core-Sidebar-Wrapper
    (app.blade.php → @livewire('fokusplan.sidebar') liegt in x-data="sidebarState()").
    Nutzt x-ui-sidebar-list / x-ui-sidebar-item; collapsed über x-show gesteuert.
--}}
<div>
    {{-- Modul-Header --}}
    <div x-show="!collapsed" class="p-3 text-sm italic text-[var(--ui-secondary)] uppercase border-b border-[var(--ui-border)] mb-2">
        Fokusplan
    </div>

    {{-- Navigation --}}
    <x-ui-sidebar-list label="Navigation">
        <x-ui-sidebar-item :href="route('fokusplan.dashboard')">
            @svg('heroicon-o-home', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Dashboard</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('fokusplan.plans.index')">
            @svg('heroicon-o-flag', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Alle Fokuspläne</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('fokusplan.dependencies.index')">
            @svg('heroicon-o-link', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Abhängigkeiten & Ressourcen</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('fokusplan.dashboard')" type="button" wire:click="createPlan">
            @svg('heroicon-o-plus', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Neuer Fokusplan</span>
        </x-ui-sidebar-item>
    </x-ui-sidebar-list>

    {{-- Fokuspläne --}}
    <x-ui-sidebar-list label="Fokuspläne">
        @forelse($plans as $plan)
            <x-ui-sidebar-item :href="route('fokusplan.plans.show', $plan)">
                @svg('heroicon-o-flag', 'w-4 h-4 text-[var(--ui-muted)]')
                <span class="ml-2 text-sm truncate">{{ $plan->title }}</span>
                <x-slot name="trailing">
                    <span class="text-xs text-[var(--ui-muted)]">{{ $plan->steps_count }}</span>
                </x-slot>
            </x-ui-sidebar-item>
        @empty
            <div class="px-2 py-1 text-xs text-[var(--ui-muted)]">Keine Pläne</div>
        @endforelse
    </x-ui-sidebar-list>

    {{-- Collapsed: nur Icons --}}
    <div x-show="collapsed" class="px-2 py-2 border-b border-[var(--ui-border)]">
        <div class="flex flex-col gap-2">
            <a href="{{ route('fokusplan.dashboard') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-home', 'w-5 h-5')
            </a>
            <a href="{{ route('fokusplan.plans.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-flag', 'w-5 h-5')
            </a>
            <a href="{{ route('fokusplan.dependencies.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-link', 'w-5 h-5')
            </a>
        </div>
    </div>
</div>
