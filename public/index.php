<?php
error_reporting(E_ALL);
ini_set("display_errors", "on");

// Importe l'autoloader de composer
// require '../vendor/autoload.php';


// Importe l'autoloader de composer
require __DIR__ . '/../vendor/autoload.php';

// On importe le namespace du Router
use Dotenv\Dotenv; 
use App\Core\Router;



// Charge le .env (index.php est dans public/, .env est à la racine)
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


// On instancie le router
$route = new Router();

// On lance l'application
$route->routes();
