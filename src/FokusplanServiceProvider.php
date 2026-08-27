<?php

namespace Platform\Fokusplan;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Platform\Core\PlatformCore;
use Platform\Core\Routing\ModuleRouter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FokusplanServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/fokusplan.php', 'fokusplan');
    }

    public function boot(): void
    {
        // Modul registrieren
        if (
            config()->has('fokusplan.routing') &&
            config()->has('fokusplan.navigation') &&
            Schema::hasTable('modules')
        ) {
            PlatformCore::registerModule([
                'key'        => 'fokusplan',
                'title'      => 'Fokusplan',
                'group'      => 'planning',
                'routing'    => config('fokusplan.routing'),
                'guard'      => config('fokusplan.guard'),
                'navigation' => config('fokusplan.navigation'),
                'sidebar'    => config('fokusplan.sidebar'),
            ]);
        }

        // Routes
        if (PlatformCore::getModule('fokusplan')) {
            ModuleRouter::group('fokusplan', function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            });
        }

        // Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Config publish
        $this->publishes([
            __DIR__ . '/../config/fokusplan.php' => config_path('fokusplan.php'),
        ], 'config');

        // Views & Livewire
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'fokusplan');
        $this->registerLivewireComponents();

        // Tools
        $this->registerTools();
    }

    protected function registerTools(): void
    {
        try {
            $registry = resolve(\Platform\Core\Tools\ToolRegistry::class);

            // Overview
            $registry->register(new \Platform\Fokusplan\Tools\FokusplanOverviewTool());

            // Plan CRUD
            $registry->register(new \Platform\Fokusplan\Tools\ListPlansTool());
            $registry->register(new \Platform\Fokusplan\Tools\GetPlanTool());
            $registry->register(new \Platform\Fokusplan\Tools\CreatePlanTool());
            $registry->register(new \Platform\Fokusplan\Tools\UpdatePlanTool());
            $registry->register(new \Platform\Fokusplan\Tools\DeletePlanTool());

            // Phase CRUD
            $registry->register(new \Platform\Fokusplan\Tools\CreatePhaseTool());
            $registry->register(new \Platform\Fokusplan\Tools\UpdatePhaseTool());
            $registry->register(new \Platform\Fokusplan\Tools\DeletePhaseTool());

            // Step CRUD
            $registry->register(new \Platform\Fokusplan\Tools\CreateStepTool());
            $registry->register(new \Platform\Fokusplan\Tools\UpdateStepTool());
            $registry->register(new \Platform\Fokusplan\Tools\DeleteStepTool());
            $registry->register(new \Platform\Fokusplan\Tools\ReorderStepsTool());
            $registry->register(new \Platform\Fokusplan\Tools\AddStepDependencyTool());
            $registry->register(new \Platform\Fokusplan\Tools\RemoveStepDependencyTool());
        } catch (\Throwable $e) {
            \Log::warning('Fokusplan: Tool-Registrierung fehlgeschlagen', ['error' => $e->getMessage()]);
        }
    }

    protected function registerLivewireComponents(): void
    {
        $basePath = __DIR__ . '/Livewire';
        $baseNamespace = 'Platform\\Fokusplan\\Livewire';
        $prefix = 'fokusplan';

        if (!is_dir($basePath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $classPath = str_replace(['/', '.php'], ['\\', ''], $relativePath);
            $class = $baseNamespace . '\\' . $classPath;

            if (!class_exists($class)) {
                continue;
            }

            $aliasPath = str_replace(['\\', '/'], '.', Str::kebab(str_replace('.php', '', $relativePath)));
            $alias = $prefix . '.' . $aliasPath;

            Livewire::component($alias, $class);
        }
    }
}
