<?php
error_reporting(E_ALL);
ini_set("display_errors", "on");
// Importe l'autoloader de composer
require '../vendor/autoload.php';

// On importe le namespace du Router
use App\Core\Router;

// On instancie le router
$route = new Router();

// On lance l'application
$route->routes();
