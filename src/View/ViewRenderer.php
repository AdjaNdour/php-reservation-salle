<?php

declare(strict_types=1);

namespace App\View;

final class ViewRenderer
{
    public function __construct(
        private FormatInterface $format
    ) {}

    public function render(string $vue, array $data = [], int $code = 200): void
    {
        echo $this->format->response($vue, $data, $code);
    }
}
