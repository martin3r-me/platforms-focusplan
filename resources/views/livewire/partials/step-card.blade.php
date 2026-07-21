{{-- Step-Karte für die Board-Ansicht. Erwartet: $step, $statuses --}}
<div @class([
        'group rounded-lg bg-[var(--ui-surface)] border border-[var(--ui-border)]/60 p-3 shadow-sm border-l-[3px]',
        'border-l-[var(--ui-success)]' => $step->status === 'done',
        'border-l-[var(--ui-warning)]' => $step->status === 'in_progress',
        'border-l-[var(--ui-muted)]' => $step->status === 'open',
    ])>
    <div class="flex items-start justify-between gap-2">
        <p class="text-sm font-semibold text-[var(--ui-secondary)] leading-snug">{{ $step->title }}</p>
        <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
            <button wire:click="editStep({{ $step->id }})" class="p-1 rounded hover:bg-[var(--ui-muted-5)] text-[var(--ui-muted)]">
                @svg('heroicon-o-pencil', 'w-3.5 h-3.5')
            </button>
            <button wire:click="deleteStep({{ $step->id }})" wire:confirm="Diesen Step wirklich löschen?"
                    class="p-1 rounded hover:bg-[var(--ui-danger)]/10 text-[var(--ui-muted)] hover:text-[var(--ui-danger)]">
                @svg('heroicon-o-trash', 'w-3.5 h-3.5')
            </button>
        </div>
    </div>

    @if($step->details)
        <p class="mt-1.5 text-xs text-[var(--ui-muted)] line-clamp-2">{{ \Illuminate\Support\Str::of($step->details)->explode("\n")->first() }}</p>
    @endif

    <div class="mt-3 flex items-center justify-between gap-2 flex-wrap">
        @if($step->deadline)
            <span class="inline-flex items-center gap-1 text-xs text-[var(--ui-muted)] tabular-nums">
                @svg('heroicon-o-calendar', 'w-3.5 h-3.5')
                {{ $step->deadline->format('d.m.Y') }}
            </span>
        @else
            <span></span>
        @endif
        @include('fokusplan::livewire.partials.step-status-select', ['step' => $step, 'statuses' => $statuses])
    </div>

    @if($step->lead)
        <div class="mt-2 inline-flex items-center gap-1.5 text-xs text-[var(--ui-muted)]">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-[var(--ui-primary)]/10 text-[var(--ui-primary)] text-[10px] font-bold">
                {{ \Illuminate\Support\Str::of($step->lead)->explode('.')->map(fn($w) => mb_substr(trim($w),0,1))->take(2)->implode('') }}
            </span>
            {{ $step->lead }}
        </div>
    @endif
</div>
