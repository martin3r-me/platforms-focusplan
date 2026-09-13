<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Fokusplan\Models\FokusplanPhase;
use Platform\Fokusplan\Services\FokusplanGoalService;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class CreateGoalTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.goals.POST';
    }

    public function getDescription(): string
    {
        return 'POST /fokusplan/goals - Fügt einer Phase ein Fokusziel hinzu, inkl. Steuerungsblock (responsible/kpi/potential/impact/risk_note/diagnosis) und SMART-Feldern (smart_specific/smart_measurable/smart_achievable/smart_relevant/smart_timebound). ERFORDERLICH: phase_id, title.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'phase_id' => ['type' => 'integer', 'description' => 'ID der Phase, der das Ziel zugeordnet wird (ERFORDERLICH).'],
                'title' => ['type' => 'string', 'description' => 'Titel des Ziels (ERFORDERLICH).'],
                'description' => ['type' => 'string', 'description' => 'Optional: Beschreibung des Ziels.'],
                'responsible' => ['type' => 'string', 'description' => 'Optional: Verantwortliche Person.'],
                'kpi' => ['type' => 'string', 'description' => 'Optional: Kennzahl(en) zur Zielerreichung.'],
                'potential' => ['type' => 'number', 'description' => 'Optional: Potenzial (numerisch).'],
                'impact' => ['type' => 'string', 'description' => 'Optional: Hebelwirkung/Impact.'],
                'risk_note' => ['type' => 'string', 'description' => 'Optional: Risikohinweis.'],
                'diagnosis' => ['type' => 'string', 'description' => 'Optional: Diagnose/Einschätzung.'],
                'smart_specific' => ['type' => 'string', 'description' => 'Optional: SMART S - Was genau soll erreicht werden?'],
                'smart_measurable' => ['type' => 'string', 'description' => 'Optional: SMART M - Woran erkennen wir die Zielerreichung?'],
                'smart_achievable' => ['type' => 'string', 'description' => 'Optional: SMART A - Was muss grundsätzlich machbar bzw. vorhanden sein?'],
                'smart_relevant' => ['type' => 'string', 'description' => 'Optional: SMART R - Warum ist das Ziel wichtig?'],
                'smart_timebound' => ['type' => 'string', 'description' => 'Optional: SMART T - Bis wann muss das Ziel erreicht sein?'],
            ],
            'required' => ['phase_id', 'title'],
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

            $phase = FokusplanPhase::whereHas('plan', fn ($q) => $q->where('team_id', $teamId))
                ->find((int) ($arguments['phase_id'] ?? 0));
            if (!$phase) {
                return ToolResult::error('NOT_FOUND', 'Phase nicht gefunden.');
            }

            $title = trim((string) ($arguments['title'] ?? ''));
            if ($title === '') {
                return ToolResult::error('VALIDATION_ERROR', 'title ist erforderlich.');
            }

            $data = ['title' => $title, 'created_by_user_id' => $context->user->id];
            foreach ([
                'description', 'responsible', 'kpi', 'potential', 'impact', 'risk_note', 'diagnosis',
                'smart_specific', 'smart_measurable', 'smart_achievable', 'smart_relevant', 'smart_timebound',
            ] as $field) {
                if (array_key_exists($field, $arguments)) {
                    $data[$field] = $arguments[$field];
                }
            }

            $goal = (new FokusplanGoalService())->createGoal($phase, $data);

            return ToolResult::success([
                'id' => $goal->id,
                'uuid' => $goal->uuid,
                'phase_id' => $phase->id,
                'title' => $goal->title,
                'position' => $goal->position,
                'message' => "Ziel '{$goal->title}' erfolgreich hinzugefügt.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Erstellen des Ziels: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['fokusplan', 'goals', 'create', 'smart'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => false,
        ];
    }
}
