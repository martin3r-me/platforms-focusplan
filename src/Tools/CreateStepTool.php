<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Fokusplan\Models\FokusplanPlan;
use Platform\Fokusplan\Models\FokusplanStep;
use Platform\Fokusplan\Services\FokusplanStepService;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class CreateStepTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.steps.POST';
    }

    public function getDescription(): string
    {
        return 'POST /fokusplan/steps - Fügt einem Fokusplan einen Step hinzu. ERFORDERLICH: plan_id, title. Optional: details, lead, kennzahl, deadline, status (open|in_progress|done).';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'plan_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Fokusplans (ERFORDERLICH).',
                ],
                'phase_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: ID der Phase, der der Step zugeordnet wird. Muss zum Plan gehören.',
                ],
                'title' => [
                    'type' => 'string',
                    'description' => 'Titel des Steps (ERFORDERLICH).',
                ],
                'details' => ['type' => 'string', 'description' => 'Optional: Details (Freitext).'],
                'lead' => ['type' => 'string', 'description' => 'Optional: Lead, z.B. "BHG.DIGITAL".'],
                'kennzahl' => ['type' => 'string', 'description' => 'Optional: Kennzahl.'],
                'deadline' => ['type' => 'string', 'description' => 'Optional: Deadline, z.B. "Ende Q1".'],
                'status' => [
                    'type' => 'string',
                    'enum' => ['open', 'in_progress', 'done'],
                    'description' => 'Optional: Status. Default: open.',
                ],
            ],
            'required' => ['plan_id', 'title'],
        ]);
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            if (!$context->user) {
                return ToolResult::error('AUTH_ERROR', 'Kein User im Kontext gefunden.');
            }

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

            $title = trim((string) ($arguments['title'] ?? ''));
            if ($title === '') {
                return ToolResult::error('VALIDATION_ERROR', 'title ist erforderlich.');
            }

            $status = $arguments['status'] ?? FokusplanStep::STATUS_OPEN;
            if (!array_key_exists($status, FokusplanStep::STATUSES)) {
                $status = FokusplanStep::STATUS_OPEN;
            }

            $phaseId = null;
            if (!empty($arguments['phase_id'])) {
                $phase = $plan->phases()->find((int) $arguments['phase_id']);
                if (!$phase) {
                    return ToolResult::error('VALIDATION_ERROR', 'phase_id gehört nicht zu diesem Plan.');
                }
                $phaseId = $phase->id;
            }

            $step = (new FokusplanStepService())->createStep($plan, [
                'fokusplan_phase_id' => $phaseId,
                'title' => $title,
                'details' => $arguments['details'] ?? null,
                'lead' => $arguments['lead'] ?? null,
                'kennzahl' => $arguments['kennzahl'] ?? null,
                'deadline' => $arguments['deadline'] ?? null,
                'status' => $status,
                'created_by_user_id' => $context->user->id,
            ]);

            return ToolResult::success([
                'id' => $step->id,
                'uuid' => $step->uuid,
                'plan_id' => $plan->id,
                'phase_id' => $step->fokusplan_phase_id,
                'title' => $step->title,
                'status' => $step->status,
                'position' => $step->position,
                'message' => "Step '{$step->title}' erfolgreich hinzugefügt.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Steps: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['fokusplan', 'steps', 'create'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => false,
        ];
    }
}
