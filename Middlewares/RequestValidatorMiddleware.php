<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Slim\Psr7\Response;

class RequestValidatorMiddleware
{
    private $validationConfig;

    public function __construct($validationConfig)
    {
        $this->validationConfig = $validationConfig;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $body = $request->getParsedBody();
        $response = new Response();
        $images = $request->getUploadedFiles();
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['fotoPedido'];
        // Verifica si se proporcionan las claves requeridas
        foreach ($this->validationConfig['required_keys'] as $key) {
            if (!isset($body[$key])) {
                // Si falta alguna clave, responde con un error
                $response = $response->withStatus(400);
                $response->getBody()->write(json_encode(['error' => "Falta la clave '$key' en el cuerpo de la solicitud"]));
                return $response;
            }
        }

        // Verifica las claves anidadas
        foreach ($this->validationConfig['nested_keys'] as $parentKey => $nestedKeyArray) {
            if (isset($body[$parentKey]) && is_array($body[$parentKey])) {
                $aux1 = $body[$parentKey];
                foreach ($nestedKeyArray as $nestedKey) {
                    // Si la clave anidada es un array, verifica cada elemento en lugar de solo la clave
                    if (is_array($nestedKey)) {
                        foreach ($nestedKey as $subKey) {
                            if (!isset($body[$parentKey][$subKey])) {
                                $aux3 = $body[$parentKey][$subKey];
                                $response = $response->withStatus(400);
                                $response->getBody()->write(json_encode(['error' => "Parametros erroneos o faltantes"]));
                                // $response->getBody()->write(json_encode(['error' => "Falta la clave '$subKey' en '$parentKey' en el cuerpo de la solicitud"]));
                                return $response;
                            }
                        }
                    } else {
                        // Si la clave anidada no es un array, verifica normalmente
                        if (!isset($body[$parentKey][$nestedKey])) {
                            $aux4 = $body[$parentKey][$nestedKey];
                            $response = $response->withStatus(400);
                            $response->getBody()->write(json_encode(['error' => "Parametros erroneos o faltantes"]));
                            // $response->getBody()->write(json_encode(['error' => "Falta la clave '$nestedKey' en '$parentKey' en el cuerpo de la solicitud"]));
                            return $response;
                        }
                    }
                }
            }
        }
        return $handler->handle($request);
    }
}
