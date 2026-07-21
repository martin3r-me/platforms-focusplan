<?php

use Platform\Fokusplan\Livewire\Dashboard;
use Platform\Fokusplan\Livewire\Plan\Index as PlanIndex;
use Platform\Fokusplan\Livewire\Plan\Show as PlanShow;

Route::get('/', Dashboard::class)->name('fokusplan.dashboard');
Route::get('/plans', PlanIndex::class)->name('fokusplan.plans.index');
Route::get('/plans/{plan}', PlanShow::class)->name('fokusplan.plans.show');
