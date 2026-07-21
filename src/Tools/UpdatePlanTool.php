<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Fokusplan\Models\FokusplanPlan;
use Platform\Fokusplan\Services\FokusplanPlanService;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class UpdatePlanTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.plans.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /fokusplan/plans/{id} - Aktualisiert einen Fokusplan. ERFORDERLICH: plan_id. Optional: title, fachbereich, responsible, year, description.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'plan_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Fokusplans (ERFORDERLICH).',
                ],
                'title' => ['type' => 'string', 'description' => 'Optional: Neuer Titel.'],
                'fachbereich' => ['type' => 'string', 'description' => 'Optional: Fachbereich.'],
                'responsible' => ['type' => 'string', 'description' => 'Optional: Verantwortlicher.'],
                'year' => ['type' => 'integer', 'description' => 'Optional: Jahr.'],
                'description' => ['type' => 'string', 'description' => 'Optional: Beschreibung.'],
            ],
            'required' => ['plan_id'],
        ]);
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

            $data = [];
            foreach (['title', 'fachbereich', 'responsible', 'description'] as $field) {
                if (array_key_exists($field, $arguments)) {
                    $data[$field] = $arguments[$field];
                }
            }
            if (array_key_exists('year', $arguments)) {
                $data['year'] = $arguments['year'] !== null ? (int) $arguments['year'] : null;
            }

            if (isset($data['title']) && trim((string) $data['title']) === '') {
                return ToolResult::error('VALIDATION_ERROR', 'title darf nicht leer sein.');
            }

            $service = new FokusplanPlanService();
            $plan = $service->updatePlan($plan, $data);

            return ToolResult::success([
                'id' => $plan->id,
                'title' => $plan->title,
                'fachbereich' => $plan->fachbereich,
                'responsible' => $plan->responsible,
                'year' => $plan->year,
                'message' => "Fokusplan '{$plan->title}' erfolgreich aktualisiert.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Fokusplans: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['fokusplan', 'plans', 'update'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => false,
        ];
    }
}
