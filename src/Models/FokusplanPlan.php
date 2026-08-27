<?php

namespace Platform\Fokusplan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\UuidV7;

class FokusplanPlan extends Model
{
    use SoftDeletes;

    protected $table = 'fokusplan_plans';

    protected $fillable = [
        'uuid',
        'team_id',
        'title',
        'fachbereich',
        'responsible',
        'year',
        'description',
        'position',
        'created_by_user_id',
    ];

    protected $casts = [
        'year' => 'integer',
        'position' => 'integer',
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

    public function team(): BelongsTo
    {
        return $this->belongsTo(\Platform\Core\Models\Team::class, 'team_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(\Platform\Core\Models\User::class, 'created_by_user_id');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(FokusplanPhase::class, 'fokusplan_plan_id')->orderBy('position');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(FokusplanStep::class, 'fokusplan_plan_id')->orderBy('position');
    }

    public function goals(): HasMany
    {
        return $this->hasMany(FokusplanGoal::class, 'fokusplan_plan_id')->orderBy('position');
    }

    // Scopes

    public function scopeForTeam($query, int $teamId)
    {
        return $query->where('team_id', $teamId);
    }
}
