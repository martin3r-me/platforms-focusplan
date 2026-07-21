{{-- Board-Ansicht: je Phase eine Spalte, Steps als Karten.
     Erwartet: $phases, $looseSteps, $statuses --}}
<div class="flex gap-4 overflow-x-auto pb-2">
    @foreach($phases as $phase)
        @php
            $phaseDone = $phase->steps->where('status', 'done')->count();
            $phaseTotal = $phase->steps->count();
        @endphp
        <div class="flex-shrink-0 w-80 rounded-xl bg-[var(--ui-muted-5)] border border-[var(--ui-border)]/60 flex flex-col">
            <div class="flex items-center justify-between gap-2 p-3 border-b border-[var(--ui-border)]/40">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-[var(--ui-primary)]/10 text-[var(--ui-primary)] text-xs font-bold flex-shrink-0">{{ $loop->iteration }}</span>
                    <span class="font-semibold text-[var(--ui-secondary)] truncate">{{ $phase->title }}</span>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                    <span class="text-xs text-[var(--ui-muted)] tabular-nums">{{ $phaseDone }}/{{ $phaseTotal }}</span>
                    <button wire:click="editPhase({{ $phase->id }})" class="p-1 rounded hover:bg-[var(--ui-border)]/40 text-[var(--ui-muted)]">
                        @svg('heroicon-o-pencil', 'w-3.5 h-3.5')
                    </button>
                    <button wire:click="deletePhase({{ $phase->id }})" wire:confirm="Phase löschen? Die Steps bleiben (ohne Phase) erhalten."
                            class="p-1 rounded hover:bg-[var(--ui-danger)]/10 text-[var(--ui-muted)] hover:text-[var(--ui-danger)]">
                        @svg('heroicon-o-trash', 'w-3.5 h-3.5')
                    </button>
                </div>
            </div>

            <div class="p-3 flex flex-col gap-2.5 flex-1">
                @forelse($phase->steps as $step)
                    @include('fokusplan::livewire.partials.step-card', ['step' => $step, 'statuses' => $statuses])
                @empty
                    <div class="text-xs text-[var(--ui-muted)] text-center py-4">Noch keine Steps</div>
                @endforelse
            </div>

            <button wire:click="addStep({{ $phase->id }})"
                    class="m-3 mt-0 flex items-center justify-center gap-1.5 py-2 text-xs text-[var(--ui-muted)] border border-dashed border-[var(--ui-border)] rounded-lg hover:text-[var(--ui-primary)] hover:border-[var(--ui-primary)]/40 transition-colors">
                @svg('heroicon-o-plus', 'w-3.5 h-3.5')
                <span>Step hinzufügen</span>
            </button>
        </div>
    @endforeach

    {{-- Ohne Phase --}}
    @if($looseSteps->isNotEmpty())
        <div class="flex-shrink-0 w-80 rounded-xl bg-[var(--ui-muted-5)] border border-[var(--ui-border)]/60 flex flex-col">
            <div class="flex items-center justify-between gap-2 p-3 border-b border-[var(--ui-border)]/40">
                <span class="font-semibold text-[var(--ui-secondary)]">Ohne Phase</span>
                <span class="text-xs text-[var(--ui-muted)] tabular-nums">{{ $looseSteps->count() }}</span>
            </div>
            <div class="p-3 flex flex-col gap-2.5 flex-1">
                @foreach($looseSteps as $step)
                    @include('fokusplan::livewire.partials.step-card', ['step' => $step, 'statuses' => $statuses])
                @endforeach
            </div>
        </div>
    @endif

    {{-- Neue Phase --}}
    <div class="flex-shrink-0 w-64 flex items-center justify-center rounded-xl border border-dashed border-[var(--ui-border)]">
        <button wire:click="addPhase" class="flex items-center gap-1.5 px-4 py-2 text-sm text-[var(--ui-muted)] hover:text-[var(--ui-primary)] transition-colors">
            @svg('heroicon-o-plus', 'w-4 h-4')
            <span>Neue Phase</span>
        </button>
    </div>
</div>
