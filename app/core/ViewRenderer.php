<?php

class ViewRenderer
{
    private string $viewsPath;
    private string $layout;
    private ?string $cssFile = null;

    public function __construct(string $viewsPath = __DIR__ . '/../views/')
    {
        $this->viewsPath = rtrim($viewsPath, '/') . '/';
        $this->layout = 'layout.php';
    }

    public function setCss(string $cssFile): void
    {
        $this->cssFile = $cssFile;
    }

    public function render(string $view, array $params = []): void
    {
        $viewFile = $this->viewsPath . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("Vue introuvable : $viewFile");
        }

        extract($params);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = $this->viewsPath . $this->layout;

        if (!file_exists($layoutFile)) {
            throw new Exception("Layout introuvable : $layoutFile");
        }

        require $layoutFile;
    }

    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    public function getCss(): ?string
    {
        return $this->cssFile;
    }
}
