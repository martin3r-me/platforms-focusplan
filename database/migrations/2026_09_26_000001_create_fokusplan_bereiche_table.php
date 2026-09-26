<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bereich als echte Zuordnung statt Freitext (Issue #825). Eine eigene,
     * schlanke Tabelle statt einer Anbindung an Organization-Entity: das
     * Organization-Modul stellt mit `OrganizationDimensionLink` zwar generische
     * Verknüpfungsmaschinerie bereit, aber jede darüber referenzierte
     * `OrganizationEntity` braucht zwingend einen `entity_type_id` — und im
     * Organization-Modul existiert (Stand heute) kein generischer Typ für
     * "Bereich/Business-Unit" (nur network_customer/system_agent/agent).
     * Fokusplan müsste also selbst einen neuen EntityType/-Group in einer
     * fremden Domain anlegen, nur um die freien Bereichsnamen aus dem
     * Prototyp (SPORTS, Bonn, KITA, …) irgendwo unterzubringen — das ist genau
     * die Parallelstruktur, die Issue #825 vermeiden wollte, nur eine Ebene
     * tiefer. Eine eigene `fokusplan_bereiche`-Tabelle (team-scoped, ein Name,
     * eine Position) ist dafür proportional und bleibt trotzdem eine "echte"
     * Zuordnung statt Freitext.
     */
    public function up(): void
    {
        Schema::create('fokusplan_bereiche', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['team_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fokusplan_bereiche');
    }
};
