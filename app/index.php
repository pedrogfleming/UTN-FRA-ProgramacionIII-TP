<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require_once "../globals.php";
require __DIR__ . '/../vendor/autoload.php';
require_once CONTROLLERS . '/UsuarioControllers.php';
require_once CONTROLLERS . '/ProductoController.php';
require_once CONTROLLERS . '/MesaController.php';
require_once CONTROLLERS . '/PedidoController.php';

require_once '../Middlewares/AuthMiddleware.php';

// Instantiate App
$app = AppFactory::create();
$app->setBasePath('/app');


$usuariosAuthMiddleware = new AuthMiddleware([ROL_ADMIN, ROL_SOCIO]);
$productosAuthMiddleware = new AuthMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_BARTENDER, ROL_CERVEZERO, ROL_MOZO, ROL_COCINERO]);
$mesasAuthMiddleware = new AuthMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_BARTENDER ,ROL_MOZO]);
$pedidosAuthMiddleware = new AuthMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_BARTENDER, ROL_CERVEZERO, ROL_MOZO, ROL_COCINERO]);

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes

$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
    $group->put('/{usuario}', \UsuarioController::class . ':ModificarUno');
    $group->delete('/{usuario}', \UsuarioController::class . ':BorrarUno');
})->add($usuariosAuthMiddleware);

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->get('/{producto}', \ProductoController::class . ':TraerUno');
    $group->post('[/]', \ProductoController::class . ':CargarUno');
    $group->put('/{producto}', \ProductoController::class . ':ModificarUno');
    $group->delete('/{producto}', \ProductoController::class . ':BorrarUno');
})->add($productosAuthMiddleware);

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->get('/{mesa}', \MesaController::class . ':TraerUno');
    $group->post('[/]', \MesaController::class . ':CargarUno');
    $group->put('/{mesa}', \MesaController::class . ':ModificarUno');
    $group->delete('/{mesa}', \MesaController::class . ':BorrarUno');
})->add($mesasAuthMiddleware);

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \PedidoController::class . ':TraerTodos');
    $group->get('/{pedido}', \PedidoController::class . ':TraerUno');
    $group->post('[/]', \PedidoController::class . ':CargarUno');
    $group->put('/{pedido}', \PedidoController::class . ':ModificarUno');
    $group->delete('/{pedido}', \PedidoController::class . ':BorrarUno');
})->add($pedidosAuthMiddleware);

$app->run();
