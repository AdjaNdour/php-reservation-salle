<?php

declare(strict_types=1);

namespace App\View;

final class HtmlFormat implements FormatInterface
{
    public function __construct(
        private string $templatesDir
    ) {
    }

    public function response(string $vue, array $data = [], int $code = 200): string
    {
        http_response_code($code);
        extract($data);

        $viewFile = $this->templatesDir . '/' . $vue . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Fichier de vue introuvable : {$viewFile}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = $this->templatesDir . '/layout/base.php';
        if (file_exists($layoutFile)) {
            ob_start();
            require $layoutFile;
            return ob_get_clean();
        }

        return $content;
    }
}