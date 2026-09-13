<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Fokusplan\Models\FokusplanGoal;
use Platform\Fokusplan\Services\FokusplanGoalService;
use Platform\Fokusplan\Tools\Concerns\ResolvesFokusplanTeam;

class UpdateGoalTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesFokusplanTeam;

    public function getName(): string
    {
        return 'fokusplan.goals.PATCH';
    }

    public function getDescription(): string
    {
        return 'PATCH /fokusplan/goals/{id} - Aktualisiert ein Fokusziel, inkl. Steuerungsblock (responsible/kpi/potential/impact/risk_note/diagnosis) und SMART-Feldern (smart_specific/smart_measurable/smart_achievable/smart_relevant/smart_timebound). ERFORDERLICH: goal_id.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'goal_id' => ['type' => 'integer', 'description' => 'ID des Ziels (ERFORDERLICH).'],
                'title' => ['type' => 'string', 'description' => 'Optional: Neuer Titel.'],
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
            'required' => ['goal_id'],
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

            $goalId = (int) ($arguments['goal_id'] ?? 0);
            if ($goalId <= 0) {
                return ToolResult::error('VALIDATION_ERROR', 'goal_id ist erforderlich.');
            }

            $goal = FokusplanGoal::whereHas('plan', fn ($q) => $q->where('team_id', $teamId))->find($goalId);
            if (!$goal) {
                return ToolResult::error('NOT_FOUND', 'Ziel nicht gefunden.');
            }

            $data = [];
            foreach ([
                'title', 'description', 'responsible', 'kpi', 'potential', 'impact', 'risk_note', 'diagnosis',
                'smart_specific', 'smart_measurable', 'smart_achievable', 'smart_relevant', 'smart_timebound',
            ] as $field) {
                if (array_key_exists($field, $arguments)) {
                    $data[$field] = $arguments[$field];
                }
            }
            if (array_key_exists('title', $data) && trim((string) $data['title']) === '') {
                return ToolResult::error('VALIDATION_ERROR', 'title darf nicht leer sein.');
            }

            $goal = (new FokusplanGoalService())->updateGoal($goal, $data);

            return ToolResult::success([
                'id' => $goal->id,
                'title' => $goal->title,
                'smart_completion_count' => $goal->smartCompletionCount(),
                'message' => "Ziel '{$goal->title}' erfolgreich aktualisiert.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler beim Aktualisieren des Ziels: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false,
            'category' => 'action',
            'tags' => ['fokusplan', 'goals', 'update', 'smart'],
            'risk_level' => 'write',
            'requires_auth' => true,
            'requires_team' => true,
            'idempotent' => false,
        ];
    }
}
