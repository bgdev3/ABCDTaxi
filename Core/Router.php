<?php
namespace App\Core;

use App\DependencyInjection\Container;
use App\Core\WhiteList;

class Router
{
    public function __construct( 
        private Container $container, 
        private WhiteList $whiteList )
    {}
    // Route les requêtes entrantes vers les contrôleurs et actions appropriés
    public function routes(): void
    {
        $controllerName = $_GET['controller'] ?? 'Home';
        $controllerName = ucfirst($controllerName);

        if (!$this->whiteList->isAllowed($controllerName)) {
            http_response_code(404);
            echo "La page recherchée n'existe pas";
            return;
        }

        $controllerClass = "\\App\\Controllers\\{$controllerName}Controller";
        $action = $_GET['action'] ?? 'index';

        $controller = $this->container->get($controllerClass);

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            echo "Action introuvable";
            return;
        }

        $params = $_GET;
        unset($params['controller'], $params['action']);
        call_user_func_array([$controller, $action], $params);
    }
}