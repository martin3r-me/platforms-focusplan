<x-ui-page>
    {{-- Navbar --}}
    <x-slot name="navbar">
        <x-ui-page-navbar title="Fokusplan" icon="heroicon-o-flag" />
    </x-slot>

    {{-- Actionbar --}}
    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Fokusplan', 'icon' => 'flag'],
            ['label' => 'Management-Übersicht'],
        ]" />
    </x-slot>

    @php
        $ampelVariant = ['done' => 'success', 'critical' => 'danger', 'warning' => 'warning', 'neutral' => 'secondary'];
        $formatEuro = fn ($value) => $value > 0 ? number_format($value, 0, ',', '.') . ' €' : '–';
    @endphp

    {{-- Linke Sidebar: "Alle Bereiche" + Bereichsliste mit Zielanzahl (Issue #825) --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Bereiche" width="w-72" :defaultOpen="true">
            <div class="p-3 space-y-1">
                <button type="button" wire:click="selectBereich()"
                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-left text-sm transition-colors
                        {{ $selectedBereichId === null ? 'bg-[var(--ui-primary)]/12 text-[var(--ui-primary)] font-semibold' : 'text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]' }}">
                    <span>Alle Bereiche</span>
                    <span class="text-xs tabular-nums {{ $selectedBereichId === null ? 'text-[var(--ui-primary)]' : 'text-[var(--ui-muted)]' }}">{{ $totalGoalCount }}</span>
                </button>

                @forelse($bereiche as $entry)
                    @php $bereich = $entry['bereich']; @endphp
                    <button type="button" wire:click="selectBereich({{ $bereich->id }})"
                        class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-left text-sm transition-colors
                            {{ $selectedBereichId === $bereich->id ? 'bg-[var(--ui-primary)]/12 text-[var(--ui-primary)] font-semibold' : 'text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]' }}">
                        <span class="truncate">{{ $bereich->name }}</span>
                        <span class="text-xs tabular-nums flex-shrink-0 {{ $selectedBereichId === $bereich->id ? 'text-[var(--ui-primary)]' : 'text-[var(--ui-muted)]' }}">{{ $entry['goalCount'] }}</span>
                    </button>
                @empty
                    <div class="px-3 py-2 text-xs text-[var(--ui-muted)]">
                        Noch keine Bereiche. Bereiche entstehen automatisch, sobald einem Fokusplan im Kopf ein Bereich zugewiesen wird.
                    </div>
                @endforelse
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    {{-- Hauptinhalt: Management-Tabelle --}}
    <x-ui-page-container>
        <div class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-base font-semibold text-[var(--ui-secondary)]">
                    {{ $selectedBereichName ?? 'Alle Bereiche' }}
                </h1>
                <div class="rounded-xl bg-[var(--ui-surface)] border border-[var(--ui-border)]/70 px-4 py-2 shadow-sm">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)] mr-2">
                        {{ $selectedBereichId === null ? 'Gesamtpotenzial' : 'Potenzial ' . $selectedBereichName }}
                    </span>
                    <span class="text-sm font-semibold text-[var(--ui-secondary)]">
                        {{ $formatEuro($selectedBereichId === null ? $totalPotentialEuro : ($bereiche->firstWhere('bereich.id', $selectedBereichId)['potentialEuro'] ?? 0.0)) }}
                    </span>
                </div>
            </div>

            @if($rows->isEmpty())
                <p class="text-sm text-[var(--ui-muted)] pl-1">Keine Fokusziele für diese Auswahl.</p>
            @else
                <x-ui-table compact="true">
                    <x-ui-table-header>
                        @if($selectedBereichId === null)
                            <x-ui-table-header-cell compact="true">Bereich</x-ui-table-header-cell>
                        @endif
                        <x-ui-table-header-cell compact="true">Fokusziel</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Verantwortlich</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Wirkung</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true" align="right">Potenzial</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Termin</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Status</x-ui-table-header-cell>
                    </x-ui-table-header>
                    <x-ui-table-body>
                        @foreach($rows as $row)
                            @php $goal = $row['goal']; @endphp
                            <x-ui-table-row compact="true" clickable="true"
                                :href="route('fokusplan.plans.show', ['plan' => $goal->plan, 'goal' => $goal->id])">
                                @if($selectedBereichId === null)
                                    <x-ui-table-cell compact="true">
                                        <span class="text-sm">{{ $row['bereichLabel'] ?: '–' }}</span>
                                    </x-ui-table-cell>
                                @endif
                                <x-ui-table-cell compact="true">
                                    <span class="text-sm font-medium text-[var(--ui-secondary)]">{{ $goal->title }}</span>
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <span class="text-sm">{{ $goal->responsible ?: '–' }}</span>
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <span class="text-sm">{{ $goal->impact ?: '–' }}</span>
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true" align="right">
                                    <span class="text-sm tabular-nums">{{ $formatEuro($row['potentialEuro']) }}</span>
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <span class="text-sm">{{ $row['deadline']?->format('d.m.Y') ?? '–' }}</span>
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <x-ui-badge :variant="$ampelVariant[$row['ampel']['key']]" size="sm">{{ $row['ampel']['label'] }}</x-ui-badge>
                                </x-ui-table-cell>
                            </x-ui-table-row>
                        @endforeach
                    </x-ui-table-body>
                </x-ui-table>
            @endif
        </div>
    </x-ui-page-container>
</x-ui-page>
