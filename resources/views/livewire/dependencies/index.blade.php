<x-ui-page>
    {{-- Navbar --}}
    <x-slot name="navbar">
        <x-ui-page-navbar title="Fokusplan" icon="heroicon-o-flag" />
    </x-slot>

    {{-- Actionbar --}}
    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Fokusplan', 'icon' => 'flag'],
            ['label' => 'Abhängigkeiten & Ressourcen'],
        ]">
            <x-ui-input-select
                name="bereichFilter"
                :options="$bereichOptions->map(fn($b) => ['value' => $b, 'label' => $b])->all()"
                optionValue="value"
                optionLabel="label"
                wire:model.live="bereichFilter"
                :nullable="true"
                nullLabel="Alle Bereiche"
            />
        </x-ui-page-actionbar>
    </x-slot>

    {{-- Hauptinhalt --}}
    <x-ui-page-container>
        <div class="space-y-8">
            @if($grouped->isEmpty())
                <div class="py-12 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[var(--ui-muted-5)] mb-4">
                        @svg('heroicon-o-link', 'w-8 h-8 text-[var(--ui-muted)]')
                    </div>
                    <p class="text-sm text-[var(--ui-muted)]">Keine Maßnahme mit Abhängigkeiten, Ressourcen oder externer Projektreferenz.</p>
                </div>
            @else
                @foreach($grouped as $bereich => $goals)
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-[var(--ui-secondary)] mb-3">
                            {{ $bereich !== '' ? $bereich : 'Ohne Bereich' }}
                        </h2>

                        <div class="space-y-6">
                            @foreach($goals as $goalTitle => $stepsInGoal)
                                <div>
                                    <h3 class="text-xs font-medium text-[var(--ui-muted)] mb-2">
                                        {{ $goalTitle !== '' ? $goalTitle : 'Ohne Ziel' }}
                                    </h3>

                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        @foreach($stepsInGoal as $step)
                                            <div class="rounded-xl bg-[var(--ui-surface)] border border-[var(--ui-border)]/70 p-4 shadow-sm">
                                                <a href="{{ route('fokusplan.plans.show', $step->plan) }}#fokusplan-step-{{ $step->id }}"
                                                   wire:navigate
                                                   class="text-sm font-semibold text-[var(--ui-secondary)] hover:text-[var(--ui-primary)] transition-colors">
                                                    {{ $step->title }}
                                                </a>

                                                @if($step->dependsOn->isNotEmpty())
                                                    <div class="mt-2.5">
                                                        <div class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)] mb-1">Wartet auf</div>
                                                        <div class="flex flex-wrap gap-1.5">
                                                            @foreach($step->dependsOn as $dependency)
                                                                @php $isCoordination = $step->isCoordinationDependency($dependency); @endphp
                                                                <a href="{{ route('fokusplan.plans.show', $dependency->plan) }}#fokusplan-step-{{ $dependency->id }}"
                                                                   wire:navigate
                                                                   title="{{ $isCoordination ? 'Koordination – anderes Ziel/Bereich' : 'Intern – gleiches Ziel' }}"
                                                                   class="inline-flex items-center gap-1 text-xs rounded-full px-2 py-0.5 border transition-colors
                                                                        {{ $isCoordination
                                                                            ? 'border-[var(--ui-primary)]/40 bg-[var(--ui-primary)]/10 text-[var(--ui-primary)] hover:bg-[var(--ui-primary)]/20'
                                                                            : 'border-[var(--ui-border)] bg-[var(--ui-muted-5)] text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]/70' }}">
                                                                    {{ $dependency->title }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if(!empty($step->resources))
                                                    <div class="mt-2.5">
                                                        <div class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)] mb-1">Benötigt</div>
                                                        <div class="flex flex-wrap gap-1.5">
                                                            @foreach($step->resources as $resource)
                                                                <span class="text-xs rounded-full px-2 py-0.5 bg-[var(--ui-warning)]/10 text-[var(--ui-warning)] border border-[var(--ui-warning)]/30">
                                                                    {{ $resource }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($step->external_project_ref)
                                                    <div class="mt-2.5">
                                                        <div class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ui-muted)] mb-1">Externes Projekt</div>
                                                        <span class="inline-flex items-center gap-1 text-xs text-[var(--ui-secondary)]">
                                                            @svg('heroicon-o-arrow-top-right-on-square', 'w-3.5 h-3.5 text-[var(--ui-muted)]')
                                                            {{ $step->external_project_ref }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </x-ui-page-container>
</x-ui-page>
