<?php

namespace Platform\Fokusplan\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class FokusplanOverviewTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'fokusplan.overview.GET';
    }

    public function getDescription(): string
    {
        return 'GET /fokusplan/overview - Erklärt das Fokusplan-Modul: Datenmodell (Pläne + Steps), Status-Werte und verfügbare Tools. Am besten ZUERST aufrufen.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => new \stdClass(),
            'required' => [],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        return ToolResult::success([
            'module' => 'Fokusplan',
            'description' => 'Fokuspläne (Aktionspläne) anlegen und ausfüllen. Ein Plan hat einen Kopf (Titel, Fachbereich, Verantwortlich, Jahr) und eine Liste von Steps.',
            'data_model' => [
                'FokusplanPlan' => [
                    'title' => 'Titel des Plans (z.B. "Fokusplan 2026 – Bankettprofi")',
                    'fachbereich' => 'Fachbereich (z.B. "Bankettprofi")',
                    'responsible' => 'Verantwortlicher',
                    'year' => 'Jahr (integer)',
                    'phases' => 'hasMany FokusplanPhase (Abschnitte)',
                ],
                'FokusplanPhase' => [
                    'title' => 'Titel der Phase (z.B. "Phase 1")',
                    'description' => 'Optionale Beschreibung',
                    'steps' => 'hasMany FokusplanStep',
                ],
                'FokusplanStep' => [
                    'phase_id' => 'Optionale Zuordnung zu einer Phase (sonst phasenlos am Plan)',
                    'goal' => 'Optional: Übergeordnetes Ziel',
                    'status_note' => 'Optional: Freitext-Status/Notiz (z.B. "Hold 06/26")',
                    'title' => 'Steps / Titel des Arbeitsschritts',
                    'details' => 'Details (Freitext, ein Punkt pro Zeile)',
                    'lead' => 'Lead / Verantwortlicher des Steps',
                    'kennzahl' => 'Kennzahl',
                    'deadline' => 'Deadline als Datum (YYYY-MM-DD, nullable)',
                    'status' => 'open | in_progress | done',
                    'position' => 'Sortierreihenfolge',
                ],
            ],
            'statuses' => [
                'open' => 'Offen',
                'in_progress' => 'In Arbeit',
                'done' => 'Erledigt',
            ],
            'related_tools' => [
                'fokusplan.plans.GET' => 'Pläne auflisten',
                'fokusplan.plans.show.GET' => 'Einzelnen Plan inkl. Steps abrufen',
                'fokusplan.plans.POST' => 'Plan erstellen',
                'fokusplan.plans.PATCH' => 'Plan aktualisieren',
                'fokusplan.plans.DELETE' => 'Plan löschen',
                'fokusplan.phases.POST' => 'Phase erstellen',
                'fokusplan.phases.PATCH' => 'Phase aktualisieren',
                'fokusplan.phases.DELETE' => 'Phase löschen',
                'fokusplan.steps.POST' => 'Step erstellen (optional phase_id)',
                'fokusplan.steps.PATCH' => 'Step aktualisieren',
                'fokusplan.steps.DELETE' => 'Step löschen',
                'fokusplan.steps.reorder.POST' => 'Steps neu sortieren',
            ],
        ]);
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => true,
            'category' => 'info',
            'tags' => ['fokusplan', 'overview'],
            'risk_level' => 'read',
            'requires_auth' => true,
            'requires_team' => false,
            'idempotent' => true,
        ];
    }
}
