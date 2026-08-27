<?php

return [
    'name' => 'Fokusplan',
    'description' => 'Fokuspläne / Aktionspläne anlegen und ausfüllen',
    'version' => '1.0.0',

    'routing' => [
        'prefix' => 'fokusplan',
        'middleware' => ['web', 'auth'],
    ],

    'guard' => 'web',

    'navigation' => [
        'main' => [
            'fokusplan' => [
                'title' => 'Fokusplan',
                'icon' => 'heroicon-o-flag',
                'route' => 'fokusplan.dashboard',
            ],
        ],
    ],

    'sidebar' => [
        'fokusplan' => [
            'title' => 'Fokusplan',
            'icon' => 'heroicon-o-flag',
            'items' => [
                'dashboard' => [
                    'title' => 'Dashboard',
                    'route' => 'fokusplan.dashboard',
                    'icon' => 'heroicon-o-home',
                ],
                'plans' => [
                    'title' => 'Fokuspläne',
                    'route' => 'fokusplan.plans.index',
                    'icon' => 'heroicon-o-flag',
                ],
                'orientation' => [
                    'title' => 'Strategische Ausrichtung',
                    'route' => 'fokusplan.orientation.index',
                    'icon' => 'heroicon-o-compass',
                ],
                'dependencies' => [
                    'title' => 'Abhängigkeiten & Ressourcen',
                    'route' => 'fokusplan.dependencies.index',
                    'icon' => 'heroicon-o-link',
                ],
            ],
        ],
    ],

    'billables' => [
        [
            'model' => \Platform\Fokusplan\Models\FokusplanPlan::class,
            'type' => 'per_item',
            'label' => 'Fokusplan',
            'description' => 'Jeder erstellte Fokusplan verursacht tägliche Kosten nach Nutzung.',
            'pricing' => [
                ['cost_per_day' => 0.005, 'start_date' => '2025-01-01', 'end_date' => null],
            ],
            'free_quota' => null,
            'min_cost' => null,
            'max_cost' => null,
            'billing_period' => 'daily',
            'start_date' => '2026-01-01',
            'end_date' => null,
            'trial_period_days' => 0,
            'discount_percent' => 0,
            'exempt_team_ids' => [],
            'priority' => 100,
            'active' => true,
        ],
    ],
];
