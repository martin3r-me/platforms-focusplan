{{-- Farbiges Status-Ampel-Dropdown für einen Step. Erwartet: $step, $statuses --}}
<select
    wire:change="setStatus({{ $step->id }}, $event.target.value)"
    @class([
        'text-xs rounded-lg border px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[var(--ui-primary)]/20',
        'bg-[var(--ui-success)]/10 border-[var(--ui-success)]/40 text-[var(--ui-success)]' => $step->status === 'done',
        'bg-[var(--ui-warning)]/10 border-[var(--ui-warning)]/40 text-[var(--ui-warning)]' => $step->status === 'in_progress',
        'bg-[var(--ui-muted-5)] border-[var(--ui-border)]/60 text-[var(--ui-secondary)]' => $step->status === 'open',
    ])
>
    @foreach($statuses as $value => $label)
        <option value="{{ $value }}" @selected($step->status === $value)>{{ $label }}</option>
    @endforeach
</select>
