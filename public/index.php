<?php
error_reporting(E_ALL);
ini_set("display_errors", "on");

// Importe l'autoloader de composer
require __DIR__ . '/../vendor/autoload.php';

// On importe le namespace du Router
use Dotenv\Dotenv; 
use App\Core\Router;
use App\DependencyInjection\Container;

// Charge le .env (index.php est dans public/, .env est à la racine)
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Instancie le container de dépendances
$container = new Container();
// On enregistre le Router dans le container pour pouvoir l'injecter dans les contrôleurs
$route = $container->get(Router::class);

// On lance l'application
$route->routes();
