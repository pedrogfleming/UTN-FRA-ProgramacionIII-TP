<?php
require_once MODELS . '/Usuario.php';
require_once UTILS . '/AutentificadorJWT.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpBadRequestException;
use Slim\Psr7\Response;


class AuthorizationMiddleware
{
    private $rolesAutorizados;

    public function __construct(array $rolesAutorizados)
    {
        $this->rolesAutorizados = $rolesAutorizados;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $headerParams = $request->getServerParams();

        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $datosUsuario = AutentificadorJWT::ObtenerData($token);

        $autorizacion = $this->isAuthorized($datosUsuario);
        if ($autorizacion->estaAutorizado) {
            return $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(['mensaje' => $autorizacion->msj]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    private function isAuthorized($datosUsuario)
    {
        $ret = new stdClass();
        $ret->estaAutorizado = true;
        $ret->msj = "Usuario autenticado con exito";

        if ($datosUsuario->tipo) {
            $rolAutorizado = in_array($datosUsuario->tipo, $this->rolesAutorizados);
            if (!$rolAutorizado) {
                $ret->estaAutorizado = false;
                $ret->msj = "Usuario no posee permisos suficientes para realizar la accion";
            }
        } else {
            $ret->estaAutorizado = false;
            $ret->msj = "Usuario inexistente";
        }
        return $ret;
    }
}
