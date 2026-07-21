<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->string('goal')->nullable()->after('fokusplan_phase_id');
            $table->text('status_note')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('fokusplan_steps', function (Blueprint $table) {
            $table->dropColumn(['goal', 'status_note']);
        });
    }
};
