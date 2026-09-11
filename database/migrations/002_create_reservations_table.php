<?php

declare(strict_types=1);

namespace Database\migrations;

use Database\MigrationInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new class implements MigrationInterface
{
    public function up(Builder $schema): void
    {
        if ($schema->hasTable('reservations')) {
            echo "La table reservations existe déjà." . PHP_EOL;
            return;
        }

        $schema->create('reservations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('salle_id');
            $table->string('responsable');
            $table->string('email');
            $table->text('motif');
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->enum('statut', [
                'confirmée',
                'annulée'
            ])->default('confirmée');

            $table->foreign('salle_id')
                ->references('id')
                ->on('salles')
                ->onDelete('cascade');

            $table->timestamps();
        });

        echo "Table reservations créée !" . PHP_EOL;
    }

    public function down(Builder $schema): void
    {
        if (!$schema->hasTable('reservations')) {
            echo "La table reservations n'existe pas." . PHP_EOL;
            return;
        }

        $schema->drop('reservations');

        echo "Table reservations supprimée !" . PHP_EOL;
    }
};
