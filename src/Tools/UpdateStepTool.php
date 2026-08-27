<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Fokusplan\Models\FokusplanStep;
use Platform\Fokusplan\Services\FokusplanStepService;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class UpdateStepTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.steps.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /fokusplan/steps/{id} - Aktualisiert einen Step. ERFORDERLICH: step_id. Optional: title, details, lead, supporters, resources, external_project_ref, kennzahl, deadline, status (open|in_progress|done).';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'step_id' => [
                    'type' => 'integer',
                    'description' => 'ID des Steps (ERFORDERLICH).',
                ],
                'phase_id' => [
                    'type' => ['integer', 'null'],
                    'description' => 'Optional: Verschiebt den Step in diese Phase (muss zum selben Plan gehören). null = aus Phase entfernen.',
                ],
                'goal' => ['type' => 'string', 'description' => 'Optional: Übergeordnetes Ziel.'],
                'title' => ['type' => 'string', 'description' => 'Optional: Neuer Titel / Maßnahme.'],
                'details' => ['type' => 'string', 'description' => 'Optional: Details.'],
                'status_note' => ['type' => 'string', 'description' => 'Optional: Freitext-Status/Notiz.'],
                'lead' => ['type' => 'string', 'description' => 'Optional: Verantwortlicher (genau eine Person/Rolle).'],
                'supporters' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Optional: Liste der Unterstützer (Personen/Rollen/Externe). Ersetzt die bisherige Liste vollständig.',
                ],
                'resources' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Optional: Benötigte Ressourcen (Budget, Freigabe, Toolzugang, Zeit, ...). Ersetzt die bisherige Liste vollständig.',
                ],
                'external_project_ref' => ['type' => ['string', 'null'], 'description' => 'Optional: Verweis auf ein externes Projekt außerhalb des Fokusplans. Leerstring/null entfernt die Referenz.'],
                'kennzahl' => ['type' => 'string', 'description' => 'Optional: Kennzahl.'],
                'deadline' => ['type' => ['string', 'null'], 'description' => 'Optional: Deadline als Datum YYYY-MM-DD. Leerstring/null entfernt die Deadline.'],
                'status' => [
                    'type' => 'string',
                    'enum' => ['open', 'in_progress', 'done'],
                    'description' => 'Optional: Status.',
                ],
            ],
            'required' => ['step_id'],
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

            $stepId = (int) ($arguments['step_id'] ?? 0);
            if ($stepId <= 0) {
                return ToolResult::error('VALIDATION_ERROR', 'step_id ist erforderlich.');
            }

            $step = FokusplanStep::whereHas('plan', fn ($q) => $q->where('team_id', $teamId))->find($stepId);
            if (!$step) {
                return ToolResult::error('NOT_FOUND', 'Step nicht gefunden.');
            }

            $data = [];
            foreach (['goal', 'title', 'details', 'lead', 'kennzahl', 'status_note'] as $field) {
                if (array_key_exists($field, $arguments)) {
                    $data[$field] = $arguments[$field];
                }
            }
            if (array_key_exists('supporters', $arguments)) {
                $data['supporters'] = FokusplanStep::normalizeSupporters($arguments['supporters']);
            }
            if (array_key_exists('resources', $arguments)) {
                $data['resources'] = FokusplanStep::normalizeResources($arguments['resources']);
            }
            if (array_key_exists('external_project_ref', $arguments)) {
                $data['external_project_ref'] = !empty($arguments['external_project_ref']) ? trim((string) $arguments['external_project_ref']) : null;
            }
            if (array_key_exists('deadline', $arguments)) {
                $data['deadline'] = !empty($arguments['deadline']) ? $arguments['deadline'] : null;
            }
            if (array_key_exists('status', $arguments)) {
                if (!array_key_exists($arguments['status'], FokusplanStep::STATUSES)) {
                    return ToolResult::error('VALIDATION_ERROR', 'Ungültiger Status. Erlaubt: open, in_progress, done.');
                }
                $data['status'] = $arguments['status'];
            }
            if (array_key_exists('phase_id', $arguments)) {
                if ($arguments['phase_id'] === null || $arguments['phase_id'] === 0) {
                    $data['fokusplan_phase_id'] = null;
                } else {
                    $phase = $step->plan?->phases()->find((int) $arguments['phase_id']);
                    if (!$phase) {
                        return ToolResult::error('VALIDATION_ERROR', 'phase_id gehört nicht zum Plan dieses Steps.');
                    }
                    $data['fokusplan_phase_id'] = $phase->id;
                }
            }
            if (isset($data['title']) && trim((string) $data['title']) === '') {
                return ToolResult::error('VALIDATION_ERROR', 'title darf nicht leer sein.');
            }

            $step = (new FokusplanStepService())->updateStep($step, $data);

            return ToolResult::success([
                'id' => $step->id,
                'title' => $step->title,
                'status' => $step->status,
                'message' => "Step '{$step->title}' erfolgreich aktualisiert.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Steps: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['fokusplan', 'steps', 'update'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => false,
        ];
    }
}
