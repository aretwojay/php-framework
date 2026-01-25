<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class DashboardController extends AbstractController
{
    public function process(Request $request): Response
    {
        return $this->index($request);
    }

    public function index(Request $request): Response
    {
        $user = ['name' => 'Admin']; // Simule l'utilisateur connecté

        return $this->render(
            'admin/dashboard',               // vue
            ['title' => 'Dashboard Admin', 'user' => $user],
            'layouts/admin'                  // layout
        );
    }

    protected function render(string $template, array $data = [], ?string $layout = null): Response
    {
        $response = new Response();
    extract($data);

        // chemin absolu correct vers les vues
        $viewsPath = __DIR__ . "/../../views"; // depuis src/Controllers -> ../../views = /var/www/html/app/views
    // vue principale
    $viewFile = "{$viewsPath}/{$template}.php";
    if (!file_exists($viewFile)) {
        throw new \Exception("Vue introuvable : $viewFile");
    }
    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    // layout si défini
    if ($layout) {
        $layoutFile = "{$viewsPath}/{$layout}.php";
        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout introuvable : $layoutFile");
        }
        ob_start();
        require $layoutFile;
        $response->setContent(ob_get_clean());
    } else {
        $response->setContent($content);
    }

    $response->addHeader('Content-Type', 'text/html');
    return $response;
    }
}