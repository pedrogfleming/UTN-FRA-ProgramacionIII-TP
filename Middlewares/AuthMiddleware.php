<?php

require_once MODELS . '/Usuario.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
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
        $route = $request->getAttribute('route');
        $sector = $route ? $route->getArgument('sector') : null;
        $rol = $route ? $route->getArgument('rol') : null;
        $contraseña = $route ? $route->getArgument('contraseña') : null;
        $nombreUsuario = $route ? $route->getArgument('nombreUsuario') : null;

        if ($this->isAuthorized($sector, $rol, $contraseña, $nombreUsuario)) {
            return $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(['mensaje' => 'No estás autorizado']);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    private function isAuthorized($sector, $rol, $contraseña, $nombreUsuario): bool
    {
        $ret = false;
        $rolAutorizado = in_array($rol, $this->rolesAutorizados);
        if ($rolAutorizado) {
            $usuarioExiste = Usuario::obtenerUsuarioByName($nombreUsuario);
            if($usuarioExiste){
                $contraseñaCorrecta = $usuarioExiste->password === $contraseña;
                if($contraseñaCorrecta){
                    $ret = true;
                }
            }
        }
        return $ret;
    }
}
