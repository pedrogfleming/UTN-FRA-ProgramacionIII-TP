<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require_once "../globals.php";
require __DIR__ . '/../vendor/autoload.php';
require_once CONTROLLERS . '/UsuarioControllers.php';
require_once CONTROLLERS . '/ProductoController.php';
require_once CONTROLLERS . '/MesaController.php';
require_once CONTROLLERS . '/PedidoController.php';
require_once CONTROLLERS . '/AuthController.php';

require_once MIDDLEWARES . '/AuthMiddleware.php';
require_once MIDDLEWARES . '/RequestValidatorMiddleware.php';
require_once UTILS . '/AutentificadorJWT.php';

// Load ENV
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();
$app->setBasePath('/app');


$usuariosAuthMiddleware = new AuthMiddleware([ROL_ADMIN, ROL_SOCIO]);
$productosAuthMiddleware = new AuthMiddleware([ROL_ADMIN, ROL_SOCIO,  ROL_CERVEZERO, ROL_MOZO, ROL_COCINERO]);
$mesasAuthMiddleware = new AuthMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_MOZO]);
$pedidosAuthMiddleware = new AuthMiddleware([ROL_ADMIN, ROL_SOCIO,  ROL_CERVEZERO, ROL_MOZO, ROL_COCINERO]);



// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

if (!file_exists(SETTINGS)) {
    echo 'Archivo settings no existe';
    exit;
}

// Routes

// JWT en login
$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->post('/login', \AuthController::class . ':GenerarToken');
    $group->get('/verificarToken', \AuthController::class . ':VerificarToken');
});



$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $contenidos = file_get_contents(SETTINGS);
    $settings = json_decode($contenidos, true);
    if ($settings === null) {
        echo 'Error al decodificar el archivo settings.';
        exit;
    }
    $cargarUnoReqValidatorKeys = $settings['usuarios']['CargarUno']['validation_config'];

    $group->get('[/]', \UsuarioController::class . ':TraerTodos')->add(\AuthMiddleware::class . ':verificarToken');
    $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno')->add(new RequestValidatorMiddleware($cargarUnoReqValidatorKeys));
    $group->put('/{usuario}', \UsuarioController::class . ':ModificarUno');
    $group->delete('/{usuario}', \UsuarioController::class . ':BorrarUno');
})->add($usuariosAuthMiddleware);

$app->group('/productos', function (RouteCollectorProxy $group) {
    $contenidos = file_get_contents(SETTINGS);
    $settings = json_decode($contenidos, true);
    if ($settings === null) {
        echo 'Error al decodificar el archivo settings.';
        exit;
    }
    $cargarUnoReqValidatorKeys = $settings['productos']['CargarUno']['validation_config'];

    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->get('/{producto}', \ProductoController::class . ':TraerUno');
    $group->post('[/]', \ProductoController::class . ':CargarUno')->add(new RequestValidatorMiddleware($cargarUnoReqValidatorKeys));
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
    $contenidos = file_get_contents(SETTINGS);
    $settings = json_decode($contenidos, true);
    if ($settings === null) {
        echo 'Error al decodificar el archivo settings.';
        exit;
    }
    $cargarUnoReqValidatorKeys = $settings['pedidos']['CargarUno']['validation_config'];


    $group->get('[/]', \PedidoController::class . ':TraerTodos');
    $group->get('/{pedido}', \PedidoController::class . ':TraerUno');
    $group->post('[/]', \PedidoController::class . ':CargarUno')->add(new RequestValidatorMiddleware($cargarUnoReqValidatorKeys));
    $group->put('/{pedido}', \PedidoController::class . ':ModificarUno');
    $group->delete('/{pedido}', \PedidoController::class . ':BorrarUno');
})->add($pedidosAuthMiddleware);

$app->run();
