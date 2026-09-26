<?php

namespace Platform\Fokusplan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\UuidV7;

/**
 * Bereich als echte Zuordnung statt Freitext (Issue #825): jeder Fokusplan
 * gehört zu genau einem Bereich (team-scoped), statt den Namen als
 * `fachbereich`-Freitext auf dem Plan selbst zu pflegen. Ermöglicht die
 * Gruppierung über mehrere Pläne hinweg (Sidebar, Management-Übersicht).
 */
class FokusplanBereich extends Model
{
    use SoftDeletes;

    protected $table = 'fokusplan_bereiche';

    protected $fillable = [
        'uuid',
        'team_id',
        'name',
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

    public function team(): BelongsTo
    {
        return $this->belongsTo(\Platform\Core\Models\Team::class, 'team_id');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(FokusplanPlan::class, 'bereich_id');
    }

    /**
     * Liefert den Bereich für einen Namen (team-scoped), legt ihn bei Bedarf
     * an. Hält die bisherige Freitext-Ergonomie ("einfach eintippen") am
     * Plan-Formular aufrecht, während der Bereich selbst eine echte,
     * wiederverwendbare Zeile bleibt statt pro Plan neu getippt zu werden.
     */
    public static function findOrCreateForTeam(int $teamId, string $name): self
    {
        $name = trim($name);

        $bereich = self::where('team_id', $teamId)->where('name', $name)->first();
        if ($bereich) {
            return $bereich;
        }

        $position = (int) self::where('team_id', $teamId)->max('position');

        return self::create([
            'team_id' => $teamId,
            'name' => $name,
            'position' => $position + 1,
        ]);
    }
}
