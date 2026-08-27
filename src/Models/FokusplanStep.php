<?php

namespace Platform\Fokusplan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\UuidV7;

class FokusplanStep extends Model
{
    use SoftDeletes;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_DONE = 'done';

    public const STATUSES = [
        self::STATUS_OPEN => 'Offen',
        self::STATUS_IN_PROGRESS => 'In Arbeit',
        self::STATUS_BLOCKED => 'Blockiert',
        self::STATUS_DONE => 'Erledigt',
    ];

    public const UNIT_EURO = 'euro';
    public const UNIT_HOURS = 'hours';
    public const UNIT_PERCENT = 'percent';

    public const UNITS = [
        self::UNIT_EURO => '€',
        self::UNIT_HOURS => 'Std.',
        self::UNIT_PERCENT => '% Effizienz',
    ];

    protected $table = 'fokusplan_steps';

    protected $fillable = [
        'uuid',
        'fokusplan_plan_id',
        'fokusplan_phase_id',
        'fokusplan_goal_id',
        'goal',
        'title',
        'details',
        'lead',
        'supporters',
        'kennzahl',
        'potential_value',
        'potential_unit',
        'deadline',
        'status',
        'status_note',
        'position',
        'created_by_user_id',
    ];

    protected $casts = [
        'position' => 'integer',
        'deadline' => 'date',
        'potential_value' => 'decimal:2',
        'supporters' => 'array',
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

    public function goal(): BelongsTo
    {
        return $this->belongsTo(FokusplanGoal::class, 'fokusplan_goal_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(\Platform\Core\Models\User::class, 'created_by_user_id');
    }

    // Scopes

    public function scopeForPlan($query, int $planId)
    {
        return $query->where('fokusplan_plan_id', $planId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeInvolvingPerson($query, string $person)
    {
        return $query->where(function ($q) use ($person) {
            $q->where('lead', $person)->orWhereJsonContains('supporters', $person);
        });
    }

    // Helpers

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    public static function normalizeSupporters(mixed $value): array
    {
        return collect(is_array($value) ? $value : [])
            ->map(fn ($s) => trim((string) $s))
            ->filter(fn ($s) => $s !== '')
            ->unique()
            ->values()
            ->all();
    }

    public function involvesPerson(string $person): bool
    {
        $person = trim($person);
        if ($person === '') {
            return false;
        }

        if ($this->lead !== null && trim($this->lead) === $person) {
            return true;
        }

        return in_array($person, $this->supporters ?? [], true);
    }
}
