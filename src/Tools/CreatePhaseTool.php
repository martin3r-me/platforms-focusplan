<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Fokusplan\Models\FokusplanPlan;
use Platform\Fokusplan\Services\FokusplanPhaseService;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class CreatePhaseTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.phases.POST';
    }

    public function getDescription(): string
    {
        return 'POST /fokusplan/phases - Fügt einem Fokusplan eine Phase (Abschnitt) hinzu. ERFORDERLICH: plan_id, title. Optional: description.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'plan_id' => ['type' => 'integer', 'description' => 'ID des Fokusplans (ERFORDERLICH).'],
                'title' => ['type' => 'string', 'description' => 'Titel der Phase, z.B. "Phase 1" (ERFORDERLICH).'],
                'description' => ['type' => 'string', 'description' => 'Optional: Beschreibung der Phase.'],
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

            $plan = FokusplanPlan::where('team_id', $teamId)->find((int) ($arguments['plan_id'] ?? 0));
            if (!$plan) {
                return ToolResult::error('NOT_FOUND', 'Fokusplan nicht gefunden.');
            }

            $title = trim((string) ($arguments['title'] ?? ''));
            if ($title === '') {
                return ToolResult::error('VALIDATION_ERROR', 'title ist erforderlich.');
            }

            $phase = (new FokusplanPhaseService())->createPhase($plan, [
                'title' => $title,
                'description' => $arguments['description'] ?? null,
                'created_by_user_id' => $context->user->id,
            ]);

            return ToolResult::success([
                'id' => $phase->id,
                'uuid' => $phase->uuid,
                'plan_id' => $plan->id,
                'title' => $phase->title,
                'position' => $phase->position,
                'message' => "Phase '{$phase->title}' erfolgreich hinzugefügt.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen der Phase: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['fokusplan', 'phases', 'create'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => false,
        ];
    }
}
