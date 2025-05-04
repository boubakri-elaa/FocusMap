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
    Schema::table('etapes', function (Blueprint $table) {
        $table->unsignedBigInteger('user_id');  // Ajoute la colonne user_id
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');  // Crée la clé étrangère
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
{
    Schema::table('etapes', function (Blueprint $table) {
        $table->dropForeign(['user_id']);  // Supprime la clé étrangère
        $table->dropColumn('user_id');  // Supprime la colonne user_id
    });
}
};
