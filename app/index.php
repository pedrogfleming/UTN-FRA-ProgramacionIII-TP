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
require_once CONTROLLERS . '/EncuestaController.php';

require_once MIDDLEWARES . '/AuthenticationMiddleware.php';
require_once MIDDLEWARES . '/AuthorizationMiddleware.php';
require_once MIDDLEWARES . '/RequestValidatorMiddleware.php';
require_once MIDDLEWARES . '/LoggerMiddleware.php';
require_once UTILS . '/AutentificadorJWT.php';

// Load ENV
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();
$app->setBasePath('/app');

// PERMISOS
$usuariosAuthorizationMiddleware = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO]);
$productosAuthorizationMiddleware = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO,  ROL_CERVECERO, ROL_MOZO, ROL_COCINERO]);
$mesasAuthorizationMiddleware = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_MOZO]);
$pedidosAuthorizationMiddleware = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO,  ROL_CERVECERO, ROL_MOZO, ROL_COCINERO]);


// Add error middleware
$app->addErrorMiddleware(true, true, true);
// Add parse body
$app->addBodyParsingMiddleware();

$app->add(new LoggerMiddleware());
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
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno')->add(new RequestValidatorMiddleware($cargarUnoReqValidatorKeys));
    $group->put('/{usuario}', \UsuarioController::class . ':ModificarUno');
    $group->delete('/{usuario}', \UsuarioController::class . ':BorrarUno');
})->add($usuariosAuthorizationMiddleware)->add(new AuthenticationMiddleware());

$app->group('/productos', function (RouteCollectorProxy $group) {
    $cargarUnoProducto = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO]);
    $modificarUnoProducto = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO]);
    $traerTodosProducto = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO,  ROL_CERVECERO, ROL_MOZO, ROL_COCINERO]);
    $borrarUnoProducto = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO]);
    $contenidos = file_get_contents(SETTINGS);
    $settings = json_decode($contenidos, true);
    if ($settings === null) {
        echo 'Error al decodificar el archivo settings.';
        exit;
    }
    $cargarUnoReqValidatorKeys = $settings['productos']['CargarUno']['validation_config'];

    $group->get('[/]', \ProductoController::class . ':TraerTodos')->add($traerTodosProducto);
    $group->get('/{producto}', \ProductoController::class . ':TraerUno')->add($traerTodosProducto);
    $group->post('[/]', \ProductoController::class . ':CargarUno')->add($cargarUnoProducto)->add(new RequestValidatorMiddleware($cargarUnoReqValidatorKeys));
    $group->put('/{producto}', \ProductoController::class . ':ModificarUno')->add($modificarUnoProducto);
    $group->delete('/{producto}', \ProductoController::class . ':BorrarUno')->add($borrarUnoProducto);
    $group->post('/cargaMasiva', \ProductoController::class . ':CargaMasiva')->add($cargarUnoProducto);
    $group->get('/bulk/descargaMasiva', \ProductoController::class . ':DescargaMasiva')->add($traerTodosProducto);
})->add(new AuthenticationMiddleware());

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $cargarUnoMesa = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_MOZO]);
    $traerTodosMesa = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_MOZO]);
    $modificarUnoMesa = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_MOZO]);
    $borrarUnoMesa = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_MOZO]);

    $group->get('[/]', \MesaController::class . ':TraerTodos')->add($traerTodosMesa);
    $group->get('/{mesa}', \MesaController::class . ':TraerUno')->add($traerTodosMesa);
    $group->post('[/]', \MesaController::class . ':CargarUno')->add($cargarUnoMesa);
    $group->put('/{mesa}', \MesaController::class . ':ModificarUno')->add($modificarUnoMesa);
    $group->delete('/{mesa}', \MesaController::class . ':BorrarUno')->add($borrarUnoMesa);
})->add(new AuthenticationMiddleware());

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $contenidos = file_get_contents(SETTINGS);
    $settings = json_decode($contenidos, true);
    if ($settings === null) {
        echo 'Error al decodificar el archivo settings.';
        exit;
    }
    $cargarUnoReqValidatorKeys = $settings['pedidos']['CargarUno']['validation_config'];

    $cargarUnoPedido = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_MOZO]);
    $traerTodosPedido = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO,  ROL_CERVECERO, ROL_MOZO, ROL_COCINERO]);
    $modificarUnoPedido = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_MOZO]);
    $borrarUnoPedido = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_MOZO]);

    $group->get('[/]', \PedidoController::class . ':TraerTodos')->add($traerTodosPedido);
    $group->get('/{pedido}', \PedidoController::class . ':TraerUno')->add($traerTodosPedido);
    $group->post('[/]', \PedidoController::class . ':CargarUno')->add($cargarUnoPedido)->add(new RequestValidatorMiddleware($cargarUnoReqValidatorKeys));
    $group->put('/{pedido}', \PedidoController::class . ':ModificarUno')->add($modificarUnoPedido);
    $group->delete('/{pedido}', \PedidoController::class . ':BorrarUno')->add($borrarUnoPedido);
})->add(new AuthenticationMiddleware());

$app->group('/encuestas', function (RouteCollectorProxy $group) {
    $contenidos = file_get_contents(SETTINGS);
    $settings = json_decode($contenidos, true);
    if ($settings === null) {
        echo 'Error al decodificar el archivo settings.';
        exit;
    }
    $cargarUnoReqValidatorKeys = $settings['encuestas']['CargarUno']['validation_config'];

    $cargarUnoEncuesta = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_MOZO]);
    $traerTodosEncuesta = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO,  ROL_CERVECERO, ROL_MOZO, ROL_COCINERO]);
    $modificarUnoEncuesta = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_MOZO]);
    $borrarUnoEncuesta = new AuthorizationMiddleware([ROL_ADMIN, ROL_SOCIO, ROL_MOZO]);

    $group->get('[/]', \EncuestaController::class . ':TraerTodos')->add($traerTodosEncuesta);
    $group->get('/{encuesta}', \EncuestaController::class . ':TraerUno')->add($traerTodosEncuesta);
    $group->post('[/]', \EncuestaController::class . ':CargarUno')->add($cargarUnoEncuesta)->add(new RequestValidatorMiddleware($cargarUnoReqValidatorKeys));
    $group->put('/{encuesta}', \EncuestaController::class . ':ModificarUno')->add($modificarUnoEncuesta);
    $group->delete('/{encuesta}', \EncuestaController::class . ':BorrarUno')->add($borrarUnoEncuesta);
})->add(new AuthenticationMiddleware());

$app->run();
