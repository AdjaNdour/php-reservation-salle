<?php

use Illuminate\Database\Capsule\Manager as Capsule;

if (!Capsule::schema()->hasTable('utilisateurs')) {
    Capsule::schema()->create('utilisateurs', function ($table) {
        $table->increments('id');
        $table->string('nom');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('role')->default('responsable');
        $table->timestamps();
    });
    echo "Table utilisateurs créée !\n";
} else {
    if (!Capsule::schema()->hasColumn('utilisateurs', 'role')) {
        Capsule::schema()->table('utilisateurs', function ($table) {
            $table->string('role')->default('responsable');
        });
        echo "Colonne 'role' ajoutée à la table utilisateurs !\n";
    } else {
        echo "La table utilisateurs existe déjà.\n";
    }
}
