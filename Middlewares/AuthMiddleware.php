<?php

require_once MODELS . '/Usuario.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Slim\Psr7\Response;


class AuthMiddleware
{
    private $rolesAutorizados;

    public function __construct(array $rolesAutorizados)
    {
        $this->rolesAutorizados = $rolesAutorizados;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $headerValueArray = $request->getHeader('Accept');
        
        $headerParams = $request->getServerParams();
        $routeContext = RouteContext::fromRequest($request);
        $args = $routeContext->getRoute()->getArguments();
        
        // $route = $request->getHeader('route');
        // $sector = $route ? $route->getHeader('sector') : null;
        // $rol = $route ? $route->getHeader('rol') : null;
        if (isset($headerParams['PHP_AUTH_USER']) && isset($headerParams['PHP_AUTH_PW'])) {
            $nombreUsuario = $headerParams['PHP_AUTH_USER'];
            $contraseña = $headerParams['PHP_AUTH_PW'];
            $autorizacion = $this->isAuthorized($nombreUsuario, $contraseña);
            if ($autorizacion->estaAutorizado) {
                return $handler->handle($request);
            } else {
                $response = new Response();
                $payload = json_encode(['mensaje' => $autorizacion->msj]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
    }

    private function isAuthorized($nombreUsuario, $contraseña)
    {
        $ret = new stdClass();
        $ret->estaAutorizado = false;
        $ret->msj = "Usuario y/o contraseña incorrectos";

        $usuarioExiste = Usuario::obtenerUsuarioByUsername($nombreUsuario);

        if($usuarioExiste){
            $rolAutorizado = in_array($usuarioExiste->tipo, $this->rolesAutorizados);
            if ($rolAutorizado) {                                
                $contraseñaCorrecta = $usuarioExiste->password === $contraseña;
                if ($contraseñaCorrecta) {
                    $ret->estaAutorizado = true;
                    $ret->msj = "Usuario autenticado con exito";
                }                
            }
            else{
                $ret->estaAutorizado = false;
                $ret->msj = "Usuario no posee permisos suficientes";
            }
        }
        return $ret;
    }
}
