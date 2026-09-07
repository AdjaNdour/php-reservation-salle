<?php

use Illuminate\Database\Capsule\Manager as Capsule;

if (!Capsule::schema()->hasTable('utilisateurs')) {
    Capsule::schema()->create('utilisateurs', function ($table) {
        $table->increments('id');
        $table->string('nom');
        $table->string('email')->unique();
        $table->string('password');
        $table->timestamps();
    });
    echo "Table utilisateurs créée !";
} else {
    echo "La table utilisateurs existe déjà.";
}
