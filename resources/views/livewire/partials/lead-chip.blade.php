{{-- Lead als Avatar-Chip. Erwartet: $lead (string|null) --}}
@if($lead)
    @php
        $initials = \Illuminate\Support\Str::of($lead)
            ->explode('.')->flatMap(fn($p) => explode(' ', $p))
            ->filter()->map(fn($w) => mb_strtoupper(mb_substr(trim($w), 0, 1)))
            ->take(2)->implode('');
    @endphp
    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-[var(--ui-secondary)] whitespace-nowrap">
        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-[var(--ui-primary)]/10 text-[var(--ui-primary)] text-[10px] font-bold leading-none">{{ $initials ?: '–' }}</span>
        {{ $lead }}
    </span>
@else
    <span class="text-sm text-[var(--ui-muted)]">–</span>
@endif
