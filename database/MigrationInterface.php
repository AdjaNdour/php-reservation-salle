<?php

declare(strict_types=1);

namespace Database;

use Illuminate\Database\Schema\Builder;

interface MigrationInterface
{
    public function up(Builder $schema): void;
    public function down(Builder $schema): void;
}