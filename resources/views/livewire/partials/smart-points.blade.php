{{-- SMART-Vollständigkeits-Indikator (Issue #826): 5 Punkte, gefüllt = weinrot, nicht erfasst = grau gestrichelt --}}
@php
    $smartPoints = $goal->smartCompletionPoints();
@endphp
<div class="flex items-center gap-1" title="SMART: {{ $goal->smartCompletionCount() }}/5 erfasst">
    @foreach($smartPoints as $point)
        <span
            title="{{ $point['label'] }}: {{ $point['filled'] ? $point['value'] : 'nicht erfasst' }}"
            class="inline-block w-2 h-2 rounded-full flex-shrink-0 {{ $point['filled'] ? 'bg-[var(--ui-danger)]' : 'border border-dashed border-[var(--ui-muted)] bg-transparent' }}"
        ></span>
    @endforeach
</div>
