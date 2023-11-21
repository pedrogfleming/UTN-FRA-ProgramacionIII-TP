<?php
require_once UTILS . '/AutentificadorJWT.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthenticationMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        try {
            AutentificadorJWT::verificarToken($token);
            return $handler->handle($request);
        } catch (Exception $e) {
            $mensaje = "";
            if($e.getcwd() == 0){
                $mensaje = "No se pudo autenticar la request: " . $e->getMessage();
            }
            else{
                $mensaje = "Error: " . $e->getMessage();
            }
            $response = new Response();
            $payload = json_encode(['mensaje' => $mensaje]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
}
