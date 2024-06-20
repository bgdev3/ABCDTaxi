<?php
namespace App\Core;

class Router
{
    public function routes(): void
    {
        // On teste si la superGlobal $_GET[controller] est déclaré et non vide, puis on ajoute l epremier index de $_GET
        // dans la variable $controller, ou par default home, ainsi que son namespace et le mot controller
        // pour compléter le nom de la classe à instancier.
        $controller = (isset($_GET['controller']) ? ucfirst(array_shift($_GET)) : 'Home');
        $controller = '\\App\\Controllers\\' . $controller . 'Controller';

        // On teste si la superGlobale est déclaré et non vide puis on ajoute le premier index 
        // à la variable $action, sinon index.
        $action = (isset($_GET['action']) ? array_shift($_GET) : 'index');

        // On instancie le controller
        $controller = new $controller();

        if (method_exists($controller, $action)) {
             // Si $_GET contient des index, on execute la methode en passant en arguments les paramètres de GET,
            // ou alors, on execute la methodes sans arguments.
            (isset($_GET)) ? call_user_func_array([$controller, $action], $_GET) : $controller->$action();
        } else {
            // Sinon on affiche la page 404
            http_response_code(404);
            echo "La page recherché n'existe pas";
        }
    }
}