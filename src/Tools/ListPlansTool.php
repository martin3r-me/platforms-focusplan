<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Fokusplan\Models\FokusplanPlan;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class ListPlansTool implements ToolContract, ToolMetadataContract
{
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.plans.GET';
    }

    public function getDescription(): string
    {
        return 'GET /fokusplan/plans - Listet alle Fokuspläne des Teams. Optional: year (Filter).';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Team-ID. Default: aktuelles Team aus Kontext.',
                ],
                'year' => [
                    'type' => 'integer',
                    'description' => 'Optional: Filtert Pläne nach Jahr.',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) {
                return $resolved['error'];
            }
            $teamId = (int) $resolved['team_id'];

            $query = FokusplanPlan::where('team_id', $teamId)
                ->withCount('steps')
                ->orderByDesc('year')
                ->orderBy('position');

            if (!empty($arguments['year'])) {
                $query->where('year', (int) $arguments['year']);
            }

            $plans = $query->get()->map(fn (FokusplanPlan $plan) => [
                'id' => $plan->id,
                'uuid' => $plan->uuid,
                'title' => $plan->title,
                'fachbereich' => $plan->fachbereich,
                'responsible' => $plan->responsible,
                'year' => $plan->year,
                'steps_count' => $plan->steps_count,
            ])->all();

            return ToolResult::success([
                'team_id' => $teamId,
                'count' => count($plans),
                'plans' => $plans,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden der Fokuspläne: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => true,
            'category' => 'query',
            'tags' => ['fokusplan', 'plans', 'list'],
            'risk_level' => 'read',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => true,
        ];
    }
}
