# Fokusplan

Schlichtes Platform-Modul zum Anlegen und Ausfüllen von **Fokusplänen** (Aktionsplänen).

Ein Fokusplan hat einen Kopf (Titel, Fachbereich, Verantwortlich, Jahr) und eine Liste von
**Steps** mit den Spalten `Steps · Details · Lead · Kennzahl · Deadline · Status`
(analog zur Vernetze-Fokusplan-Vorlage).

## Datenmodell

- `fokusplan_plans` — team-scoped, UUIDv7
- `fokusplan_steps` — gehört zu einem Plan, Status `open` / `in_progress` / `done`, sortierbar

## Namespace / Konventionen

- Namespace: `Platform\Fokusplan\...`
- Views: `fokusplan::livewire.xxx`
- Routes: `fokusplan.xxx`
- Composer: `martin3r/platforms-fokusplan`

## LLM-Tools

Overview, Plan-CRUD (List/Get/Create/Update/Delete), Step-CRUD (Create/Update/Delete) + Reorder.

## Installation in einer Instance

```jsonc
// composer.json → repositories
{ "type": "vcs", "url": "https://github.com/martin3r-me/platforms-fokusplan.git" }
// composer.json → require
"martin3r/platforms-fokusplan": "dev-main"
```

```bash
composer update martin3r/platforms-fokusplan
php artisan migrate
```
