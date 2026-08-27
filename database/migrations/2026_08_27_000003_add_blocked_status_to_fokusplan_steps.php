<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE fokusplan_steps MODIFY status ENUM('open', 'in_progress', 'blocked', 'done') NOT NULL DEFAULT 'open'");
    }

    public function down(): void
    {
        // Blockierte Steps zurück auf "In Arbeit", bevor der Wert aus dem Enum fällt.
        DB::table('fokusplan_steps')->where('status', 'blocked')->update(['status' => 'in_progress']);

        DB::statement("ALTER TABLE fokusplan_steps MODIFY status ENUM('open', 'in_progress', 'done') NOT NULL DEFAULT 'open'");
    }
};
