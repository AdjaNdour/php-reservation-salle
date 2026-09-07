<?php

use Illuminate\Database\Capsule\Manager as Capsule;

if (!Capsule::schema()->hasTable('reservations')) {
    Capsule::schema()->create('reservations', function ($table) {
        $table->increments('id');

        $table->unsignedInteger('salle_id');

        $table->string('responsable');
        $table->string('email');
        $table->text('motif');

        $table->dateTime('date_debut');
        $table->dateTime('date_fin');

        $table->enum('statut', ['confirmée', 'annulée'])->default('confirmée');

        $table->foreign('salle_id')
            ->references('id')
            ->on('salles')
            ->onDelete('cascade');

        $table->timestamps();
    });
    echo "Table reservations créée !";
} else {
    echo "La table reservations existe déjà.";
}
