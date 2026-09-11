<?php

declare(strict_types=1);

namespace App\View;

final class JsonFormat implements FormatInterface
{
    public function response(string $vue,array $data = [],int $code = 200): string {
        http_response_code($code);

        header('Content-Type: application/json; charset=utf-8');

        return json_encode(
            $data,
            JSON_THROW_ON_ERROR
        );
    }
}