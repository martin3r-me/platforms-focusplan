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
     * Bereich des Ziels für die Ausrichtungsseite (Issue #831): pragmatisch über
     * das bestehende fachbereich-Freitextfeld des Plans gelöst (analog #828),
     * solange #825 (Bereich als echte Entität) offen ist.
     */
    public function bereichLabel(): string
    {
        $plan = $this->plan;
        if (!$plan) {
            return '';
        }

        return trim($plan->fachbereich ?: $plan->title);
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
