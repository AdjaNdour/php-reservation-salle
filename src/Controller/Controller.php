<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Middleware\Interface\IMiddleware;
use App\View\ViewRenderer;

abstract class Controller
{
    public function __construct(
        protected ?ViewRenderer $view = null
    ) {
    }

    protected function middleware(IMiddleware $middleware): bool
    {
        return $middleware->handle();
    }

    protected function render(string $template, array $data = [], int $code = 200, ?string $format = null): void
    {
        $this->view?->render($template, $data, $code, $format);
    }
}
