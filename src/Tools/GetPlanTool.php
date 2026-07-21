<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Fokusplan\Models\FokusplanPlan;
use Platform\Fokusplan\Models\FokusplanStep;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class GetPlanTool implements ToolContract, ToolMetadataContract
{
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.plans.show.GET';
    }

    public function getDescription(): string
    {
        return 'GET /fokusplan/plans/{id} - Liefert einen Fokusplan inkl. aller Steps. ERFORDERLICH: plan_id.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'plan_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Fokusplans (ERFORDERLICH).',
                ],
            ],
            'required' => ['plan_id'],
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

            $planId = (int) ($arguments['plan_id'] ?? 0);
            if ($planId <= 0) {
                return ToolResult::error('VALIDATION_ERROR', 'plan_id ist erforderlich.');
            }

            $plan = FokusplanPlan::where('team_id', $teamId)->with('steps')->find($planId);
            if (!$plan) {
                return ToolResult::error('NOT_FOUND', 'Fokusplan nicht gefunden. Nutze "fokusplan.plans.GET".');
            }

            return ToolResult::success([
                'id' => $plan->id,
                'uuid' => $plan->uuid,
                'title' => $plan->title,
                'fachbereich' => $plan->fachbereich,
                'responsible' => $plan->responsible,
                'year' => $plan->year,
                'description' => $plan->description,
                'steps' => $plan->steps->map(fn (FokusplanStep $step) => [
                    'id' => $step->id,
                    'uuid' => $step->uuid,
                    'title' => $step->title,
                    'details' => $step->details,
                    'lead' => $step->lead,
                    'kennzahl' => $step->kennzahl,
                    'deadline' => $step->deadline,
                    'status' => $step->status,
                    'position' => $step->position,
                ])->all(),
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Laden des Fokusplans: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => true,
            'category' => 'query',
            'tags' => ['fokusplan', 'plans', 'get'],
            'risk_level' => 'read',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => true,
        ];
    }
}
