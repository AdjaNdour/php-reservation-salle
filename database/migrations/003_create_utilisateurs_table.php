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
        if (!$schema->hasTable('utilisateurs')) {
            $schema->create('utilisateurs', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('nom');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role')->default('responsable');
                $table->timestamps();
            });

            echo "Table utilisateurs créée !" . PHP_EOL;
            return;
        }

        if (!$schema->hasColumn('utilisateurs', 'role')) {
            $schema->table('utilisateurs', function (Blueprint $table): void {
                $table->string('role')->default('responsable');
            });

            echo "Colonne 'role' ajoutée à la table utilisateurs !" . PHP_EOL;
            return;
        }

        echo "La table utilisateurs existe déjà." . PHP_EOL;
    }

    public function down(Builder $schema): void
    {
        if (!$schema->hasTable('utilisateurs')) {
            echo "La table utilisateurs n'existe pas." . PHP_EOL;
            return;
        }

        $schema->drop('utilisateurs');

        echo "Table utilisateurs supprimée !" . PHP_EOL;
    }
};
