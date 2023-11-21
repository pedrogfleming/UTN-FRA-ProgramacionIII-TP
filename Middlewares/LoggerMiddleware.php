<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class LoggerMiddleware
{
    private $logs = [];

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        $requestData = [
            'method' => $request->getMethod(),
            'uri' => (string)$request->getUri(),
            'headers' => $request->getHeaders(),
            'body' => (string)$request->getBody()
        ];
        $responseData = [
            'statusCode' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string)$response->getBody()
        ];
        $logData = [
            'request' => $requestData,
            'response' => $responseData,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        $this->logs[] = $logData;

        $this->saveToFile(LOGGIN_FILE);

        return $response;
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function saveToFile($filename)
    {
        $logJson = json_encode($this->logs, JSON_PRETTY_PRINT);
        if (file_exists($filename)) {
            $logJson = ',' . PHP_EOL . $logJson;
        }
        // Utiliza el modo de escritura 'a' para agregar al final del archivo
        file_put_contents($filename, $logJson, FILE_APPEND);
    }
}
