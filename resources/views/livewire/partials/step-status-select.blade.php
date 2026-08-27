{{-- Status als farbige Pill (nativer Select darunter → inline editierbar, kein Clipping).
     Erwartet: $step, $statuses --}}
@php
    $pill = match ($step->status) {
        'done' => 'bg-[var(--ui-success)]/12 text-[var(--ui-success)]',
        'in_progress' => 'bg-[var(--ui-warning)]/15 text-[var(--ui-warning)]',
        'blocked' => 'bg-[var(--ui-danger)]/12 text-[var(--ui-danger)]',
        default => 'bg-[var(--ui-muted-5)] text-[var(--ui-secondary)]',
    };
    $dot = match ($step->status) {
        'done' => 'bg-[var(--ui-success)]',
        'in_progress' => 'bg-[var(--ui-warning)]',
        'blocked' => 'bg-[var(--ui-danger)]',
        default => 'bg-[var(--ui-muted)]',
    };
@endphp
<label class="relative inline-flex items-center rounded-full {{ $pill }} pl-2.5 pr-6 py-1 cursor-pointer transition-colors hover:brightness-95">
    <span class="w-1.5 h-1.5 rounded-full {{ $dot }} mr-1.5 flex-shrink-0"></span>
    <span class="text-xs font-medium whitespace-nowrap">{{ $statuses[$step->status] ?? $step->status }}</span>
    @svg('heroicon-o-chevron-down', 'w-3 h-3 absolute right-2 opacity-60 pointer-events-none')
    <select
        wire:change="setStatus({{ $step->id }}, $event.target.value)"
        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
        aria-label="Status ändern"
    >
        @foreach($statuses as $value => $label)
            <option value="{{ $value }}" @selected($step->status === $value)>{{ $label }}</option>
        @endforeach
    </select>
</label>
