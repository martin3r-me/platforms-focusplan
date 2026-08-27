<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Fokusplan\Models\FokusplanStep;
use Platform\Fokusplan\Services\FokusplanStepService;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class AddStepDependencyTool implements ToolContract, ToolMetadataContract
{
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.steps.dependencies.POST';
    }

    public function getDescription(): string
    {
        return 'POST /fokusplan/steps/{id}/dependencies - Fügt eine "Wartet auf"-Abhängigkeit zwischen zwei Maßnahmen hinzu. ERFORDERLICH: step_id, depends_on_step_id. Verhindert Selbstreferenz und Zyklen.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'step_id' => ['type' => 'integer', 'description' => 'ID des Steps, der wartet (ERFORDERLICH).'],
                'depends_on_step_id' => ['type' => 'integer', 'description' => 'ID der vorgelagerten Maßnahme, auf die gewartet wird (ERFORDERLICH).'],
            ],
            'required' => ['step_id', 'depends_on_step_id'],
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
            $dependsOnStepId = (int) ($arguments['depends_on_step_id'] ?? 0);
            if ($stepId <= 0 || $dependsOnStepId <= 0) {
                return ToolResult::error('VALIDATION_ERROR', 'step_id und depends_on_step_id sind erforderlich.');
            }

            $step = FokusplanStep::whereHas('plan', fn ($q) => $q->where('team_id', $teamId))->find($stepId);
            if (!$step) {
                return ToolResult::error('NOT_FOUND', 'Step nicht gefunden.');
            }

            $dependsOn = FokusplanStep::whereHas('plan', fn ($q) => $q->where('team_id', $teamId))->find($dependsOnStepId);
            if (!$dependsOn) {
                return ToolResult::error('NOT_FOUND', 'Vorgelagerte Maßnahme nicht gefunden.');
            }

            (new FokusplanStepService())->addDependency($step, $dependsOn);

            return ToolResult::success([
                'step_id' => $step->id,
                'depends_on_step_id' => $dependsOn->id,
                'message' => "Step '{$step->title}' wartet jetzt auf '{$dependsOn->title}'.",
            ]);
        } catch (\DomainException $e) {
            return ToolResult::error('VALIDATION_ERROR', $e->getMessage());
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Anlegen der Abhängigkeit: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['fokusplan', 'steps', 'dependencies'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => true,
        ];
    }
}
