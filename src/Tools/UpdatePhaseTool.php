<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Fokusplan\Models\FokusplanPhase;
use Platform\Fokusplan\Services\FokusplanPhaseService;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class UpdatePhaseTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.phases.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /fokusplan/phases/{id} - Aktualisiert eine Phase. ERFORDERLICH: phase_id. Optional: title, description.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'phase_id' => ['type' => 'integer', 'description' => 'ID der Phase (ERFORDERLICH).'],
                'title' => ['type' => 'string', 'description' => 'Optional: Neuer Titel.'],
                'description' => ['type' => 'string', 'description' => 'Optional: Beschreibung.'],
            ],
            'required' => ['phase_id'],
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

            $phaseId = (int) ($arguments['phase_id'] ?? 0);
            if ($phaseId <= 0) {
                return ToolResult::error('VALIDATION_ERROR', 'phase_id ist erforderlich.');
            }

            $phase = FokusplanPhase::whereHas('plan', fn ($q) => $q->where('team_id', $teamId))->find($phaseId);
            if (!$phase) {
                return ToolResult::error('NOT_FOUND', 'Phase nicht gefunden.');
            }

            $data = [];
            foreach (['title', 'description'] as $field) {
                if (array_key_exists($field, $arguments)) {
                    $data[$field] = $arguments[$field];
                }
            }
            if (isset($data['title']) && trim((string) $data['title']) === '') {
                return ToolResult::error('VALIDATION_ERROR', 'title darf nicht leer sein.');
            }

            $phase = (new FokusplanPhaseService())->updatePhase($phase, $data);

            return ToolResult::success([
                'id' => $phase->id,
                'title' => $phase->title,
                'message' => "Phase '{$phase->title}' erfolgreich aktualisiert.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren der Phase: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['fokusplan', 'phases', 'update'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => false,
        ];
    }
}
