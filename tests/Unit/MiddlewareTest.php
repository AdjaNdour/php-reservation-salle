<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controller\Middleware\AdminMiddleware;
use App\Controller\Middleware\AuthMiddleware;
use App\Service\Interface\InterfaceAuthService;
use App\Session\SessionManager;
use App\View\FormatInterface;
use App\View\ViewRenderer;
use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    private InterfaceAuthService $authService;
    private SessionManager $sessionManager;
    private FormatInterface $format;
    private ViewRenderer $view;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->authService = $this->createMock(InterfaceAuthService::class);
        $this->sessionManager = new SessionManager();
        $this->format = $this->createMock(FormatInterface::class);
        $this->view = new ViewRenderer($this->format);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testAuthMiddlewareAutoriseSiConnecte(): void
    {
        $this->authService->method('estConnecte')->willReturn(true);

        $middleware = new AuthMiddleware($this->authService, $this->sessionManager);
        $this->assertTrue($middleware->handle());
    }

    public function testAuthMiddlewareRefuseSiNonConnecte(): void
    {
        $this->authService->method('estConnecte')->willReturn(false);

        $middleware = new AuthMiddleware($this->authService, $this->sessionManager);
        $this->assertFalse($middleware->handle());
        $this->assertSame(
            "Veuillez vous connecter pour accéder à l'application.",
            $this->sessionManager->getData('flash_error')
        );
    }

    public function testAdminMiddlewareAutoriseSiAdmin(): void
    {
        $this->authService->method('estAdmin')->willReturn(true);

        $middleware = new AdminMiddleware($this->authService, $this->view);
        $this->assertTrue($middleware->handle());
    }

    public function testAdminMiddlewareRefuseSiNonAdmin(): void
    {
        $this->authService->method('estAdmin')->willReturn(false);

        $this->format->expects($this->once())
            ->method('response')
            ->with('error/403', $this->anything(), 403)
            ->willReturn('403');

        $middleware = new AdminMiddleware($this->authService, $this->view);

        ob_start();
        $result = $middleware->handle();
        ob_end_clean();

        $this->assertFalse($result);
    }
}
