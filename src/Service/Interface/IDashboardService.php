<?php

declare(strict_types=1);

namespace App\Service\Interface;

interface IDashboardService
{
    public function getMostUsedSalles(int $limit = 5): array;

    public function getGlobalStatistics(): array;
}
