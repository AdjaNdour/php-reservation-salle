<?php

declare(strict_types=1);

namespace App\Session\Interface;

interface ISessionManager
{
    public function startSession(): void;

    public function getData(string $key, mixed $default = null): mixed;

    public function saveData(string $key, mixed $value): void;

    public function has(string $key): bool;

    public function removeData(string $key): void;

    public function destroySession(): void;
}
