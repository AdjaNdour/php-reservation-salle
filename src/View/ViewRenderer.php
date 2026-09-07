<?php

declare(strict_types=1);

namespace App\View;

class ViewRenderer
{
    private string $templatesDir;

    public function __construct(?string $templatesDir = null)
    {
        $this->templatesDir = $templatesDir ?? dirname(__DIR__, 2) . '/templates';
    }

    public function render(string $template, array $data = []): void
    {
        extract($data);

        $viewFile = $this->templatesDir . '/' . $template . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Fichier de vue introuvable : {$viewFile}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = $this->templatesDir . '/layout/base.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }
}
