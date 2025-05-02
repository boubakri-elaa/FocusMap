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
    // database/migrations/xxxx_xx_xx_xxxxxx_remove_adresse_latitude_longitude_from_objectifs_table.php
public function up()
{
    Schema::table('objectifs', function (Blueprint $table) {
        $table->dropColumn(['adresse', 'latitude', 'longitude']);
    });
}

public function down()
{
    Schema::table('objectifs', function (Blueprint $table) {
        $table->string('adresse')->nullable();
        $table->decimal('latitude', 10, 8)->nullable();
        $table->decimal('longitude', 11, 8)->nullable();
    });
}

};
