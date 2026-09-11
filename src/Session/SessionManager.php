<?php

declare(strict_types=1);

namespace App\Session;

use App\Session\Interface\ISessionManager;

class SessionManager implements ISessionManager
{
    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    public function getData(string $key, mixed $value = null): mixed
    {
        $this->startSession();
        return $_SESSION[$key] ?? $value;
    }

    public function saveData(string $key, mixed $value): void
    {
        $this->startSession();
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        $this->startSession();
        return isset($_SESSION[$key]);
    }

    public function removeData(string $key): void
    {
        $this->startSession();
        unset($_SESSION[$key]);
    }

    public function destroySession(): void
    {
        $this->startSession();
        $_SESSION = [];

        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
