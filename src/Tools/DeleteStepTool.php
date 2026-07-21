<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Fokusplan\Models\FokusplanStep;
use Platform\Fokusplan\Services\FokusplanStepService;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class DeleteStepTool implements ToolContract, ToolMetadataContract
{
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.steps.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /fokusplan/steps/{id} - Löscht einen Step. ERFORDERLICH: step_id.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'step_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Steps (ERFORDERLICH).',
                ],
            ],
            'required' => ['step_id'],
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

            $stepId = (int) ($arguments['step_id'] ?? 0);
            if ($stepId <= 0) {
                return ToolResult::error('VALIDATION_ERROR', 'step_id ist erforderlich.');
            }

            $step = FokusplanStep::whereHas('plan', fn ($q) => $q->where('team_id', $teamId))->find($stepId);
            if (!$step) {
                return ToolResult::error('NOT_FOUND', 'Step nicht gefunden.');
            }

            $title = $step->title;
            (new FokusplanStepService())->deleteStep($step);

            return ToolResult::success([
                'deleted' => true,
                'message' => "Step '{$title}' erfolgreich gelöscht.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Löschen des Steps: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['fokusplan', 'steps', 'delete'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => false,
        ];
    }
}
