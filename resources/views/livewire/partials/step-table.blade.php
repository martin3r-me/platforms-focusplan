{{-- Aktionsplan-Tabelle (Mockup-Stil, Platform-Tokens).
     Erwartet: $steps (Collection), $statuses (array), $phaseId (int|null) --}}
@php
    $th = 'text-left text-[11px] font-semibold uppercase tracking-wider text-[var(--ui-muted)] px-3.5 py-2.5 whitespace-nowrap';
    $td = 'px-3.5 py-3 align-top';
@endphp
@if($steps->isNotEmpty())
    <div class="overflow-x-auto rounded-xl border border-[var(--ui-border)]/60">
        <table class="w-full text-sm min-w-[780px]" style="border-collapse:collapse">
            <thead>
                <tr class="bg-[var(--ui-muted-5)]/60 border-b border-[var(--ui-border)]/60">
                    <th class="{{ $th }}">Steps</th>
                    <th class="{{ $th }}">Details</th>
                    <th class="{{ $th }}">Lead</th>
                    <th class="{{ $th }}">Kennzahl</th>
                    <th class="{{ $th }}">Deadline</th>
                    <th class="{{ $th }}">Status</th>
                    <th class="{{ $th }} text-right">Aktionen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($steps as $step)
                    <tr class="group border-b border-[var(--ui-border)]/40 last:border-0 hover:bg-[var(--ui-muted-5)]/40 transition-colors">
                        <td class="{{ $td }}">
                            @if($step->goal)
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-primary)] mb-0.5 max-w-[15rem]">{{ $step->goal }}</div>
                            @endif
                            <div class="font-semibold text-[var(--ui-secondary)] max-w-[15rem] leading-snug">{{ $step->title }}</div>
                        </td>
                        <td class="{{ $td }}">
                            @if($step->details)
                                <div class="text-xs text-[var(--ui-muted)] whitespace-pre-line leading-relaxed max-w-sm">{{ $step->details }}</div>
                            @else
                                <span class="text-xs text-[var(--ui-muted)]">–</span>
                            @endif
                        </td>
                        <td class="{{ $td }}">
                            @include('fokusplan::livewire.partials.lead-chip', ['lead' => $step->lead])
                        </td>
                        <td class="{{ $td }}">
                            <span class="text-sm {{ $step->kennzahl ? 'text-[var(--ui-secondary)]' : 'text-[var(--ui-muted)]' }}">{{ $step->kennzahl ?: '–' }}</span>
                        </td>
                        <td class="{{ $td }}">
                            @if($step->deadline)
                                <span class="inline-flex items-center gap-1.5 text-sm text-[var(--ui-secondary)] tabular-nums whitespace-nowrap">
                                    @svg('heroicon-o-calendar', 'w-3.5 h-3.5 text-[var(--ui-muted)]')
                                    {{ $step->deadline->format('d.m.Y') }}
                                </span>
                            @else
                                <span class="text-sm text-[var(--ui-muted)]">–</span>
                            @endif
                        </td>
                        <td class="{{ $td }}">
                            @include('fokusplan::livewire.partials.step-status-select', ['step' => $step, 'statuses' => $statuses])
                            @if($step->status_note)
                                <div class="text-xs text-[var(--ui-muted)] mt-1.5 max-w-[12rem] leading-snug">{{ $step->status_note }}</div>
                            @endif
                        </td>
                        <td class="{{ $td }} text-right">
                            <div class="inline-flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="editStep({{ $step->id }})" class="p-1.5 rounded-lg text-[var(--ui-muted)] hover:text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)] transition-colors" title="Bearbeiten">
                                    @svg('heroicon-o-pencil', 'w-4 h-4')
                                </button>
                                <button wire:click="deleteStep({{ $step->id }})" wire:confirm="Diesen Step wirklich löschen?"
                                        class="p-1.5 rounded-lg text-[var(--ui-muted)] hover:text-[var(--ui-danger)] hover:bg-[var(--ui-danger)]/10 transition-colors" title="Löschen">
                                    @svg('heroicon-o-trash', 'w-4 h-4')
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="py-8 text-center rounded-xl border border-dashed border-[var(--ui-border)]/70">
        <p class="text-sm text-[var(--ui-muted)] mb-3">Noch keine Steps.</p>
        <x-ui-button variant="secondary-outline" size="sm" wire:click="addStep({{ $phaseId ?? 'null' }})">
            <span class="flex items-center gap-2">@svg('heroicon-o-plus', 'w-4 h-4')<span>Step hinzufügen</span></span>
        </x-ui-button>
    </div>
@endif
