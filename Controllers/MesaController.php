<?php
require_once MODELS . '/Mesa.php';
require_once INTERFACES . '/IApiUsable.php';

class MesaController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        // Crear una mesa
        $mesa = new Mesa();

        $mesa->estado = Mesa::ESTADO_CERRADA;
        $idMesaCreada = $mesa->crearMesa();

        $payload = json_encode(array("mensaje" => "Mesa creada con éxito", "id" => $idMesaCreada));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args["mesa"];
        $mesa = Mesa::obtenerMesa($id);

        if (!$mesa) {
            $ret = new stdClass();
            $ret->err = "No se encontró la mesa con el ID solicitado";
            $err_payload = json_encode($ret);
            $response->getBody()->write($err_payload);
        } else {
            $payload = json_encode($mesa);
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::obtenerTodasLasMesas();
        $payload = json_encode(array("listaMesas" => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {        
        $parametros = $request->getParsedBody();
        $estado = $parametros['estado'];
        $idMesa = $args['mesa'];

        $mesa = Mesa::obtenerMesa($idMesa);
        if($mesa){
            $mesa->estado = $estado;
            Mesa::actualizarMesa($mesa);
        }
        else{
            throw new Exception("No se encontro la mesa con el id enviado");            
        }
        $payload = json_encode(array("mensaje" => "Mesa modificada con éxito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['mesa'];
        $mesa = Mesa::obtenerMesa($id);
        $mesa->eliminarMesa();

        $payload = json_encode(array("mensaje" => "Mesa eliminada con éxito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}