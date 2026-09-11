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
        if ($schema->hasTable('salles')) {
            echo "La table salles existe déjà." . PHP_EOL;
            return;
        }

        $schema->create('salles', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nom');
            $table->string('batiment');
            $table->integer('capacite');
            $table->string('type');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        echo "Table salles créée !" . PHP_EOL;
    }

    public function down(Builder $schema): void
    {
        if (!$schema->hasTable('salles')) {
            echo "La table salles n'existe pas." . PHP_EOL;
            return;
        }

        $schema->drop('salles');

        echo "Table salles supprimée !" . PHP_EOL;
    }
};
