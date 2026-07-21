<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Fokusplan\Services\FokusplanPlanService;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class CreatePlanTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.plans.POST';
    }

    public function getDescription(): string
    {
        return 'POST /fokusplan/plans - Erstellt einen neuen Fokusplan. ERFORDERLICH: title. Optional: fachbereich, responsible, year, description.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'team_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Team-ID. Default: aktuelles Team aus Kontext.',
                ],
                'title' => [
                    'type' => 'string',
                    'description' => 'Titel des Fokusplans (ERFORDERLICH), z.B. "Fokusplan 2026".',
                ],
                'fachbereich' => [
                    'type' => 'string',
                    'description' => 'Optional: Fachbereich, z.B. "BANKETTPROFI PHASE 1".',
                ],
                'responsible' => [
                    'type' => 'string',
                    'description' => 'Optional: Verantwortlicher.',
                ],
                'year' => [
                    'type' => 'integer',
                    'description' => 'Optional: Jahr, z.B. 2026.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional: Beschreibung.',
                ],
            ],
            'required' => ['title'],
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

            $title = trim((string) ($arguments['title'] ?? ''));
            if ($title === '') {
                return ToolResult::error('VALIDATION_ERROR', 'title ist erforderlich.');
            }

            $service = new FokusplanPlanService();
            $plan = $service->createPlan([
                'title' => $title,
                'fachbereich' => $arguments['fachbereich'] ?? null,
                'responsible' => $arguments['responsible'] ?? null,
                'year' => isset($arguments['year']) ? (int) $arguments['year'] : null,
                'description' => $arguments['description'] ?? null,
                'team_id' => $teamId,
                'created_by_user_id' => $context->user->id,
            ]);

            return ToolResult::success([
                'id' => $plan->id,
                'uuid' => $plan->uuid,
                'title' => $plan->title,
                'fachbereich' => $plan->fachbereich,
                'year' => $plan->year,
                'team_id' => $plan->team_id,
                'message' => "Fokusplan '{$plan->title}' erfolgreich erstellt.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Fokusplans: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['fokusplan', 'plans', 'create'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => false,
        ];
    }
}
