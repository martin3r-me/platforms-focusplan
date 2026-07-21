<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Fokusplan\Models\FokusplanPhase;
use Platform\Fokusplan\Services\FokusplanPhaseService;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class DeletePhaseTool implements ToolContract, ToolMetadataContract
{
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.phases.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /fokusplan/phases/{id} - Löscht eine Phase. Die Steps bleiben im Plan erhalten (ohne Phase). ERFORDERLICH: phase_id.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'phase_id' => ['type' => 'integer', 'description' => 'ID der Phase (ERFORDERLICH).'],
            ],
            'required' => ['phase_id'],
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

            $phaseId = (int) ($arguments['phase_id'] ?? 0);
            if ($phaseId <= 0) {
                return ToolResult::error('VALIDATION_ERROR', 'phase_id ist erforderlich.');
            }

            $phase = FokusplanPhase::whereHas('plan', fn ($q) => $q->where('team_id', $teamId))->find($phaseId);
            if (!$phase) {
                return ToolResult::error('NOT_FOUND', 'Phase nicht gefunden.');
            }

            $title = $phase->title;
            (new FokusplanPhaseService())->deletePhase($phase);

            return ToolResult::success([
                'deleted' => true,
                'message' => "Phase '{$title}' erfolgreich gelöscht.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Löschen der Phase: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['fokusplan', 'phases', 'delete'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => false,
        ];
    }
}
