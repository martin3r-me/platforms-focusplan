<x-ui-page>
    {{-- Navbar --}}
    <x-slot name="navbar">
        <x-ui-page-navbar title="Fokusplan" icon="heroicon-o-flag" />
    </x-slot>

    {{-- Actionbar --}}
    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Fokusplan', 'icon' => 'flag'],
            ['label' => 'Strategische Ausrichtung'],
        ]" />
    </x-slot>

    @php
        $ampelPill = fn ($key) => match ($key) {
            'done' => 'bg-[var(--ui-success)]/12 text-[var(--ui-success)]',
            'warning' => 'bg-[var(--ui-warning)]/15 text-[var(--ui-warning)]',
            'critical' => 'bg-[var(--ui-danger)]/12 text-[var(--ui-danger)]',
            default => 'bg-[var(--ui-muted-5)] text-[var(--ui-secondary)]',
        };

        $formatPotential = function (array $sums) {
            if (empty($sums)) {
                return '–';
            }

            $labels = \Platform\Fokusplan\Models\FokusplanStep::UNITS;

            return collect($sums)
                ->map(fn ($value, $unit) => number_format($value, 0, ',', '.') . ' ' . ($labels[$unit] ?? $unit))
                ->implode(' · ');
        };
    @endphp

    {{-- Hauptinhalt --}}
    <x-ui-page-container>
        <div class="space-y-8">
            {{-- Gesamtsumme (dedupliziert, jedes Ziel zählt nur einmal) --}}
            <div class="rounded-xl bg-[var(--ui-surface)] border border-[var(--ui-border)]/70 p-4 shadow-sm">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)] mb-1">Gesamtpotenzial (alle Ziele, ohne Doppelzählung)</div>
                <div class="text-lg font-semibold text-[var(--ui-secondary)]">{{ $formatPotential($totalPotential) }}</div>
            </div>

            @foreach($groups as $group)
                @php $category = $group['category']; @endphp
                <div>
                    <div class="flex flex-wrap items-baseline justify-between gap-2 mb-3">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-[var(--ui-secondary)]">
                            {{ $category->title }}
                        </h2>
                        <div class="text-xs text-[var(--ui-muted)] flex items-center gap-3">
                            <span>{{ $group['goalCount'] }} {{ $group['goalCount'] === 1 ? 'Ziel' : 'Ziele' }}</span>
                            <span>{{ $group['bereichCount'] }} {{ $group['bereichCount'] === 1 ? 'Bereich' : 'Bereiche' }}</span>
                            <span title="Summe der Ziele, die auf diese Kategorie einzahlen — bei n:m-Zuordnung mehrfach beteiligt, siehe Gesamtsumme oben">
                                beteiligt an {{ $formatPotential($group['potential']) }}
                            </span>
                        </div>
                    </div>

                    @if($group['goals']->isEmpty())
                        <p class="text-sm text-[var(--ui-muted)] pl-1">Noch kein Ziel zugeordnet.</p>
                    @else
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            @foreach($group['goals'] as $goal)
                                @php $ampel = $goal->statusAmpel(); @endphp
                                <a href="{{ route('fokusplan.plans.show', $goal->plan) }}#fokusplan-goal-{{ $goal->id }}"
                                   wire:navigate
                                   class="block rounded-xl bg-[var(--ui-surface)] border border-[var(--ui-border)]/70 p-4 shadow-sm hover:border-[var(--ui-primary)]/40 transition-colors">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="text-sm font-semibold text-[var(--ui-secondary)]">{{ $goal->title }}</span>
                                        <span class="inline-flex items-center rounded-full {{ $ampelPill($ampel['key']) }} px-2 py-0.5 text-[11px] font-medium whitespace-nowrap">
                                            {{ $ampel['label'] }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-[var(--ui-muted)] mt-1.5">
                                        {{ $goal->bereichLabel() !== '' ? $goal->bereichLabel() : 'Ohne Bereich' }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Ohne Ausrichtung: der eigentliche Befund laut Ticket --}}
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wider text-[var(--ui-danger)] mb-3">
                    Ohne Ausrichtung
                </h2>

                @if($unassigned->isEmpty())
                    <p class="text-sm text-[var(--ui-muted)] pl-1">Jedes Ziel zahlt auf mindestens eine Stoßrichtung ein.</p>
                @else
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        @foreach($unassigned as $goal)
                            @php $ampel = $goal->statusAmpel(); @endphp
                            <a href="{{ route('fokusplan.plans.show', $goal->plan) }}#fokusplan-goal-{{ $goal->id }}"
                               wire:navigate
                               class="block rounded-xl bg-[var(--ui-surface)] border border-[var(--ui-danger)]/30 p-4 shadow-sm hover:border-[var(--ui-danger)]/60 transition-colors">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-sm font-semibold text-[var(--ui-secondary)]">{{ $goal->title }}</span>
                                    <span class="inline-flex items-center rounded-full {{ $ampelPill($ampel['key']) }} px-2 py-0.5 text-[11px] font-medium whitespace-nowrap">
                                        {{ $ampel['label'] }}
                                    </span>
                                </div>
                                <div class="text-xs text-[var(--ui-muted)] mt-1.5">
                                    {{ $goal->bereichLabel() !== '' ? $goal->bereichLabel() : 'Ohne Bereich' }}
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-ui-page-container>
</x-ui-page>
