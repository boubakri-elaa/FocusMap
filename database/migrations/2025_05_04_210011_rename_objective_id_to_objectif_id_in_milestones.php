<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('milestones', function (Blueprint $table) {
            $table->renameColumn('objective_id', 'objectif_id');
        });
    }

    public function down(): void
    {
        Schema::table('milestones', function (Blueprint $table) {
            $table->renameColumn('objectif_id', 'objective_id');
        });
    }
};