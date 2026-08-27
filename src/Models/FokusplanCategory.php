<?php

namespace Platform\Fokusplan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Symfony\Component\Uid\UuidV7;

class FokusplanCategory extends Model
{
    protected $table = 'fokusplan_categories';

    /**
     * Sechs Stoßrichtungen aus dem Prototyp (siehe Migration
     * 2026_08_27_000006_create_fokusplan_categories_table).
     */
    public const DEFAULT_TITLES = [
        'Kosteneffizienz & Produktivität',
        'Digitalisierung & Systeme',
        'Mitarbeiter & Nachfolge',
        'Kundenzufriedenheit & Qualität',
        'Umsatzwachstum & neue Geschäftsfelder',
        'Compliance & Risikomanagement',
    ];

    protected $fillable = [
        'uuid',
        'team_id',
        'title',
        'position',
    ];

    protected $casts = [
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

    public function goals(): BelongsToMany
    {
        return $this->belongsToMany(FokusplanGoal::class, 'fokusplan_goal_category')
            ->withTimestamps();
    }

    /**
     * Legt die sechs Stammdaten-Kategorien für ein Team an, falls noch keine
     * existieren. Idempotent, damit neue Teams die Kategorien beim ersten
     * Aufruf der Ausrichtungsseite automatisch bekommen (Issue #831).
     */
    public static function ensureDefaultsForTeam(int $teamId): void
    {
        if (self::where('team_id', $teamId)->exists()) {
            return;
        }

        foreach (self::DEFAULT_TITLES as $position => $title) {
            self::create([
                'team_id' => $teamId,
                'title' => $title,
                'position' => $position,
            ]);
        }
    }
}
