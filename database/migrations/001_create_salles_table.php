<?php

use Illuminate\Database\Capsule\Manager as Capsule;

if (!Capsule::schema()->hasTable('salles')) {
    Capsule::schema()->create('salles', function ($table) {
        $table->increments('id');
        $table->string('nom');
        $table->string('batiment');
        $table->integer('capacite');
        $table->string('type');
        $table->boolean('active')->default(true);
        $table->timestamps();
    });
    echo "Table salles créée !";
} else {
    echo "La table salles existe déjà.";
}
