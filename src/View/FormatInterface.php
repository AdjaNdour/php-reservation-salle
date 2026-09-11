<?php

declare(strict_types=1);

namespace App\View;

interface FormatInterface
{
    public function response(string $vue, array $data = [], int $code = 200): string;
}
