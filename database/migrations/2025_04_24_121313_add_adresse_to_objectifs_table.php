<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('objectifs', function (Blueprint $table) {
        $table->string('adresse')->nullable(); // ou 'text' si tu préfères un champ de plus grande taille
    });
}

public function down()
{
    Schema::table('objectifs', function (Blueprint $table) {
        $table->dropColumn('adresse');
    });
}

};
