<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Fokusplan\Models\FokusplanPlan;
use Platform\Fokusplan\Services\FokusplanStepService;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class ReorderStepsTool implements ToolContract, ToolMetadataContract
{
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.steps.reorder.POST';
    }

    public function getDescription(): string
    {
        return 'POST /fokusplan/steps/reorder - Setzt die Reihenfolge der Steps eines Plans neu. ERFORDERLICH: plan_id, step_ids (Array in gewünschter Reihenfolge).';
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
                'step_ids' => [
                    'type' => 'array',
                    'items' => ['type' => 'integer'],
                    'description' => 'Step-IDs in gewünschter Reihenfolge (ERFORDERLICH).',
                ],
            ],
            'required' => ['plan_id', 'step_ids'],
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
            $plan = FokusplanPlan::where('team_id', $teamId)->find($planId);
            if (!$plan) {
                return ToolResult::error('NOT_FOUND', 'Fokusplan nicht gefunden.');
            }

            $stepIds = $arguments['step_ids'] ?? [];
            if (!is_array($stepIds) || count($stepIds) === 0) {
                return ToolResult::error('VALIDATION_ERROR', 'step_ids muss ein nicht-leeres Array sein.');
            }

            $stepIds = array_map('intval', $stepIds);
            (new FokusplanStepService())->reorderSteps($plan->id, $stepIds);

            return ToolResult::success([
                'plan_id' => $plan->id,
                'ordered_step_ids' => $stepIds,
                'message' => 'Reihenfolge erfolgreich aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Sortieren der Steps: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['fokusplan', 'steps', 'reorder'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => true,
        ];
    }
}
