<?php
require_once MODELS . '/Encuesta.php';
require_once INTERFACES . '/IApiUsable.php';

class EncuestaController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idPedido = $parametros['idPedido'];
        $idMesa = $parametros['idMesa'];
        $idMozo = $parametros['idMozo'];
        $idCocinero = $parametros['idCocinero'];
        $puntuacionMesa = $parametros['puntuacionMesa'];
        $puntuacionRestaurante = $parametros['puntuacionRestaurante'];
        $puntuacionMozo = $parametros['puntuacionMozo'];
        $puntuacionCocinero = $parametros['puntuacionCocinero'];
        $experienciaTexto = $parametros['experienciaTexto'];

        // Creamos la encuesta
        $encuesta = new Encuesta();
        $encuesta->idPedido = $idPedido;
        $encuesta->idMesa = $idMesa;
        $encuesta->idMozo = $idMozo;
        $encuesta->idCocinero = $idCocinero;
        $encuesta->puntuacionMesa = $puntuacionMesa;
        $encuesta->puntuacionRestaurante = $puntuacionRestaurante;
        $encuesta->puntuacionMozo = $puntuacionMozo;
        $encuesta->puntuacionCocinero = $puntuacionCocinero;
        $encuesta->experienciaTexto = $experienciaTexto;
        $encuesta->fechaCreacion = new DateTime("now", new DateTimeZone("America/Argentina/Buenos_Aires"));
        $idEncuesta = $encuesta->guardarEncuesta();

        require_once MODELS . '/Mesa.php';
        $mesaCerrada = Mesa::obtenerMesa($encuesta->idMesa);
        $mesaCerrada->estado = Mesa::ESTADO_CERRADA;
        Mesa::actualizarMesa($mesaCerrada);

        $payload = json_encode(array(
            "mensaje" => "Encuesta creada con éxito",
            "id" => $idEncuesta
        ));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args["encuesta"];
        $encuesta = Encuesta::obtenerEncuesta($id);
        if (!$encuesta) {
            $ret = new stdClass();
            $ret->err = "No se encontró la encuesta con el ID solicitado";
            $err_payload = json_encode($ret);
            $response->getBody()->write($err_payload);
        } else {
            $payload = json_encode($encuesta);
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $queryParams = $request->getQueryParams();
        $idMesa = isset($queryParams['idMesa']) ? $queryParams['idMesa'] : null;
        $idMozo = isset($queryParams['idMozo']) ? $queryParams['idMozo'] : null;
        $idCocinero = isset($queryParams['idCocinero']) ? $queryParams['idCocinero'] : null;
        $puntuacionMesa = isset($queryParams['puntuacionMesa']) ? $queryParams['puntuacionMesa'] : null;
        $puntuacionRestaurante = isset($queryParams['puntuacionRestaurante']) ? $queryParams['puntuacionRestaurante'] : null;
        $puntuacionMozo = isset($queryParams['puntuacionMozo']) ? $queryParams['puntuacionMozo'] : null;
    
        $lista = Encuesta::obtenerTodos($idMesa, $idMozo, $idCocinero, $puntuacionMesa, $puntuacionRestaurante, $puntuacionMozo);
        $payload = json_encode($lista);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $args['encuesta'];
        $idMesa = $parametros['idMesa'];
        $idMozo = $parametros['idMozo'];
        $idCocinero = $parametros['idCocinero'];
        $puntuacionMesa = $parametros['puntuacionMesa'];
        $puntuacionRestaurante = $parametros['puntuacionRestaurante'];
        $puntuacionMozo = $parametros['puntuacionMozo'];
        $puntuacionCocinero = $parametros['puntuacionCocinero'];
        $experienciaTexto = $parametros['experienciaTexto'];

        $encuesta = Encuesta::obtenerEncuesta($id);

        $encuesta->idMesa = $idMesa;
        $encuesta->idMozo = $idMozo;
        $encuesta->idCocinero = $idCocinero;
        $encuesta->puntuacionMesa = $puntuacionMesa;
        $encuesta->puntuacionRestaurante = $puntuacionRestaurante;
        $encuesta->puntuacionMozo = $puntuacionMozo;
        $encuesta->puntuacionCocinero = $puntuacionCocinero;
        $encuesta->experienciaTexto = $experienciaTexto;

        Encuesta::modificarEncuesta($encuesta);

        $payload = json_encode(array("mensaje" => "Encuesta modificada con éxito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['encuesta'];
        $encuesta = Encuesta::obtenerEncuesta($id);
        Encuesta::borrarEncuesta($encuesta);

        $payload = json_encode(array("mensaje" => "Encuesta borrada con éxito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
