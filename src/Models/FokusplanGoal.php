<?php

namespace Platform\Fokusplan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\UuidV7;

class FokusplanGoal extends Model
{
    use SoftDeletes;

    public const AMPEL_DONE = 'done';
    public const AMPEL_CRITICAL = 'critical';
    public const AMPEL_WARNING = 'warning';
    public const AMPEL_NEUTRAL = 'neutral';

    public const AMPEL_LABELS = [
        self::AMPEL_DONE => 'Erledigt',
        self::AMPEL_CRITICAL => 'Kritisch',
        self::AMPEL_WARNING => 'In Arbeit',
        self::AMPEL_NEUTRAL => 'In Arbeit',
    ];

    protected $table = 'fokusplan_goals';

    public const SMART_FIELDS = [
        'smart_specific' => 'Spezifisch',
        'smart_measurable' => 'Messbar',
        'smart_achievable' => 'Ausführbar',
        'smart_relevant' => 'Relevant',
        'smart_timebound' => 'Terminiert',
    ];

    protected $fillable = [
        'uuid',
        'fokusplan_plan_id',
        'fokusplan_phase_id',
        'title',
        'description',
        'responsible',
        'kpi',
        'potential',
        'impact',
        'risk_note',
        'diagnosis',
        'smart_specific',
        'smart_measurable',
        'smart_achievable',
        'smart_relevant',
        'smart_timebound',
        'position',
        'created_by_user_id',
    ];

    protected $casts = [
        'position' => 'integer',
        'potential' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                do {
                    $uuid = UuidV7::generate();
                } while (self::where('uuid', $uuid)->exists());
                $model->uuid = $uuid;
            }
        });
    }

    // Relationships

    public function plan(): BelongsTo
    {
        return $this->belongsTo(FokusplanPlan::class, 'fokusplan_plan_id');
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(FokusplanPhase::class, 'fokusplan_phase_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(FokusplanStep::class, 'fokusplan_goal_id')->orderBy('position');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(FokusplanCategory::class, 'fokusplan_goal_category')
            ->withTimestamps();
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(\Platform\Core\Models\User::class, 'created_by_user_id');
    }

    /**
     * Bereich des Ziels (Issue #825: echte Zuordnung über FokusplanBereich
     * statt des früheren `fachbereich`-Freitextfelds), delegiert an den Plan.
     */
    public function bereichLabel(): string
    {
        return $this->plan?->bereichLabel() ?? '';
    }

    /**
     * Nächster relevanter Termin für die Management-Übersicht (Issue #825):
     * die früheste noch offene Step-Deadline, analog zur Overdue-Prüfung in
     * statusAmpel(). Kein eigenes Datumsfeld am Ziel, da der Termin sich aus
     * den Maßnahmen ergibt (gleiche Herleitung wie Fortschritt/Ampel).
     */
    public function nextDeadline(): ?\Illuminate\Support\Carbon
    {
        $openWithDeadline = $this->steps
            ->filter(fn (FokusplanStep $step) => $step->status !== FokusplanStep::STATUS_DONE && $step->deadline !== null)
            ->sortBy('deadline');

        if ($openWithDeadline->isNotEmpty()) {
            return $openWithDeadline->first()->deadline;
        }

        return $this->steps->pluck('deadline')->filter()->sort()->last();
    }

    /**
     * Potenzial des Ziels, von unten aus den Step-Kennzahlen hochgerechnet
     * (Issue #831) statt aus dem manuell gepflegten `potential`-Feld des
     * Steuerungsblocks (#827) — beide Felder bedienen unterschiedliche Zwecke.
     * Da Einheiten (Euro/Stunden/Prozent) nicht addierbar sind, wird je Einheit
     * separat summiert.
     *
     * @return array<string, float> z.B. ['euro' => 12000.0, 'hours' => 40.0]
     */
    public function potentialByUnit(): array
    {
        $sums = [];

        foreach ($this->steps as $step) {
            if ($step->potential_value === null || $step->potential_unit === null) {
                continue;
            }

            $unit = $step->potential_unit;
            $sums[$unit] = ($sums[$unit] ?? 0.0) + (float) $step->potential_value;
        }

        return $sums;
    }

    /**
     * SMART-Vollständigkeit je Feld für den Punkte-Indikator (Issue #826).
     * "nicht erfasst" ist der explizite Zustand für ein leeres Feld — kein
     * separates Statusfeld nötig, da leer/gefüllt hier bereits eindeutig ist.
     *
     * @return array<int, array{key: string, label: string, filled: bool, value: ?string}>
     */
    public function smartCompletionPoints(): array
    {
        return collect(self::SMART_FIELDS)->map(function (string $label, string $key) {
            $value = $this->{$key};

            return [
                'key' => $key,
                'label' => $label,
                'filled' => $value !== null && trim($value) !== '',
                'value' => $value,
            ];
        })->values()->all();
    }

    public function smartCompletionCount(): int
    {
        return collect($this->smartCompletionPoints())->where('filled', true)->count();
    }

    // Steuerung (Issue #827)

    /**
     * Fortschritt in Prozent plus (x/y Maßnahmen).
     *
     * @return array{percent: int, done: int, total: int}
     */
    public function progress(): array
    {
        $steps = $this->steps;
        $total = $steps->count();
        $done = $steps->where('status', FokusplanStep::STATUS_DONE)->count();

        return [
            'percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
            'done' => $done,
            'total' => $total,
        ];
    }

    /**
     * Status-Ampel, abgeleitet aus den Maßnahmen und deren Terminen (nicht manuell gepflegt).
     *
     * Regel (siehe Issue #827, Prototyp-Funktion `zielStatus`; blockiert-Fall aus #830):
     * 1. alle Maßnahmen erledigt -> Grün "Erledigt"
     * 2. sonst Termin überschritten ODER mindestens eine Maßnahme blockiert -> Rot "Kritisch"
     * 3. sonst mindestens eine Maßnahme läuft -> Gelb "In Arbeit"
     * 4. sonst neutral "In Arbeit"
     *
     * Eine blockierte Maßnahme braucht eine Entscheidung (siehe #830) und darf das Ziel
     * daher nie unter "In Arbeit" verstecken — sie zählt wie eine überschrittene Deadline.
     *
     * @return array{key: string, label: string}
     */
    public function statusAmpel(): array
    {
        $steps = $this->steps;

        if ($steps->isEmpty()) {
            return ['key' => self::AMPEL_NEUTRAL, 'label' => self::AMPEL_LABELS[self::AMPEL_NEUTRAL]];
        }

        if ($steps->every(fn (FokusplanStep $step) => $step->status === FokusplanStep::STATUS_DONE)) {
            return ['key' => self::AMPEL_DONE, 'label' => self::AMPEL_LABELS[self::AMPEL_DONE]];
        }

        $today = now()->startOfDay();
        $isOverdue = $steps->contains(function (FokusplanStep $step) use ($today) {
            return $step->status !== FokusplanStep::STATUS_DONE
                && $step->deadline !== null
                && $step->deadline->startOfDay()->lt($today);
        });

        $hasBlocked = $steps->contains(fn (FokusplanStep $step) => $step->status === FokusplanStep::STATUS_BLOCKED);

        if ($isOverdue || $hasBlocked) {
            return ['key' => self::AMPEL_CRITICAL, 'label' => self::AMPEL_LABELS[self::AMPEL_CRITICAL]];
        }

        $hasInProgress = $steps->contains(fn (FokusplanStep $step) => $step->status === FokusplanStep::STATUS_IN_PROGRESS);

        return $hasInProgress
            ? ['key' => self::AMPEL_WARNING, 'label' => self::AMPEL_LABELS[self::AMPEL_WARNING]]
            : ['key' => self::AMPEL_NEUTRAL, 'label' => self::AMPEL_LABELS[self::AMPEL_NEUTRAL]];
    }
}
