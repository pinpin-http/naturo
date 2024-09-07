<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_action_logs', function (Blueprint $table) {
            $table->string('log_color')->nullable()->after('details'); // Ajoute la colonne log_color après details
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_action_logs', function (Blueprint $table) {
            $table->dropColumn('log_color'); // Supprime la colonne si on fait un rollback
        });
    }
};
