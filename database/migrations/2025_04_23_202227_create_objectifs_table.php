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
    Schema::create('objectifs', function (Blueprint $table) {
        $table->id();
        $table->string('titre');
        $table->text('description');
        $table->string('type');
        $table->date('deadline');
        $table->string('lieu');
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Associe l'objectif à un utilisateur
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
        Schema::dropIfExists('objectifs');
    }
};
