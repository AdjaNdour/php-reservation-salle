<?php

declare(strict_types=1);

namespace App\Controller\Middleware\Interface;

interface MiddlewareInterface
{
    public function handle(): bool;
}
