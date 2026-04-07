<?php
declare(strict_types=1);

namespace App\Support;

final class View
{
    public static function render(string $template, array $data = [], string $layout = 'layouts/main'): void
    {
        $viewsRoot = dirname(__DIR__, 2) . '/views/';
        $templatePath = $viewsRoot . $template . '.php';
        $layoutPath = $viewsRoot . $layout . '.php';

        extract($data, EXTR_SKIP);

        ob_start();
        require $templatePath;
        $content = (string) ob_get_clean();

        require $layoutPath;
    }
}

