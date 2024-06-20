<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Envms\FluentPDO\Query;

$templatesDirectory = __DIR__ . '/../src/templates';
$loader = new FilesystemLoader($templatesDirectory);
$twig = new Environment($loader);

$dispatcher = simpleDispatcher(function(RouteCollector $route) {
    $route->addRoute('GET', '/', 'App\\controllers\\CarController::index');
    $route->addRoute('POST', '/add-car', 'App\\controllers\\CarController::addCar');
    $route->addRoute('POST', '/assign-parking', 'App\\controllers\\CarController::assignParking');
    $route->addRoute('POST', '/delete-car', 'App\\controllers\\CarController::deleteCar');
    $route->addRoute('POST', '/delete-parking', 'App\\controllers\\CarController::deleteParking');
    $route->addRoute('POST', '/add-parking', 'App\\controllers\\CarController::addParking');
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo '404 Nicht Gefunden';
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo '405 Methode Nicht Erlaubt';
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        [$class, $method] = explode('::', $handler);
        $pdo1 = new PDO('mysql:host=localhost;dbname=cars_db', 'root', '');
        $pdo2 = new PDO('mysql:host=localhost;dbname=parking_db', 'root', '');
        $query1 = new Query($pdo1);
        $query2 = new Query($pdo2);
        $controller = new $class($twig, $query1, $query2);
        $controller->$method($vars);
        break;
}
