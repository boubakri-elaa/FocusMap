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
    Schema::create('etapes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('objectif_id')->constrained()->onDelete('cascade'); // Référence à l'objectif
        $table->string('titre'); // Titre de l'étape
        $table->text('description'); // Description de l'étape
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('etapes');
    }
};
