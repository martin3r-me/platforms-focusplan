{{-- Verantwortlicher (hervorgehoben) + Unterstützer (neutral) als Chips. Erwartet: $lead (string|null), $supporters (array|null) --}}
@php
    $supporterChips = collect($supporters ?? [])->filter()->values();
@endphp
@if($lead || $supporterChips->isNotEmpty())
    <div class="flex flex-wrap items-center gap-1.5">
        @if($lead)
            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[var(--ui-primary)] text-white text-xs font-medium whitespace-nowrap">
                {{ $lead }}
            </span>
        @endif
        @foreach($supporterChips as $supporter)
            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[var(--ui-muted-5)] text-[var(--ui-secondary)] border border-[var(--ui-border)]/60 text-xs font-medium whitespace-nowrap">
                {{ $supporter }}
            </span>
        @endforeach
    </div>
@else
    <span class="text-sm text-[var(--ui-muted)]">–</span>
@endif
