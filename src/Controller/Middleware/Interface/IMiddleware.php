<?php

declare(strict_types=1);

namespace App\Controller\Middleware\Interface;

interface IMiddleware
{
    public function handle(): bool;
}
