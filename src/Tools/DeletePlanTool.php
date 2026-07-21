<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Fokusplan\Models\FokusplanPlan;
use Platform\Fokusplan\Services\FokusplanPlanService;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class DeletePlanTool implements ToolContract, ToolMetadataContract
{
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.plans.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /fokusplan/plans/{id} - Löscht einen Fokusplan inkl. aller Steps. ERFORDERLICH: plan_id.';
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

            $plan = FokusplanPlan::where('team_id', $teamId)->find($planId);
            if (!$plan) {
                return ToolResult::error('NOT_FOUND', 'Fokusplan nicht gefunden.');
            }

            $title = $plan->title;
            (new FokusplanPlanService())->deletePlan($plan);

            return ToolResult::success([
                'deleted' => true,
                'message' => "Fokusplan '{$title}' erfolgreich gelöscht.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Löschen des Fokusplans: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['fokusplan', 'plans', 'delete'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => false,
        ];
    }
}
