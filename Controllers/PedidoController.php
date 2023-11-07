<?php

use Illuminate\Support\Facades\Process;

include_once MODELS . '/Pedido.php';
include_once MODELS . "/Comanda.php";
include_once MODELS . "/Producto.php";
include_once MODELS . "/Usuario.php";
require_once INTERFACES . '/IApiUsable.php';

class PedidoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $idComada = $parametros['idComanda'];
        $nombreEmpleado = $parametros['nombreEmpleado'];
        $nombreProducto = $parametros['nombreProducto'];
        $cantidad = $parametros['cantidad'];

        // Creamos el pedido
        $pedido = new Pedido();
        $pedido->comanda = Comanda::obtenerComanda($idComada);
        $pedido->usuarioAsignado = Usuario::obtenerUsuarioByName($nombreEmpleado);

        $pedido->producto = new Producto();
        $pedido->producto->titulo = $nombreProducto;
        
        $pedido->producto->idProducto = $pedido->producto->crearProducto();

        $pedido->producto = Producto::obtenerProductoByName($nombreProducto);

        $pedido->cantidad = (int)$cantidad;

        $pedido->crearPedido();

        $payload = json_encode(array("mensaje" => "Pedido creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args["pedido"];
        $pedido = Pedido::obtenerPedido($id);
        if(!$pedido){
            $ret = new stdClass();
            $ret->err = "no se encontro el pedido con el id solicitado";
            $err_payload = json_encode($ret);
            $response->getBody()->write($err_payload);
        }
        else{
            $payload = json_encode($pedido);
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::obtenerTodos();
        $payload = json_encode(array("listaPedido" => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $args['pedido'];
        $nombreProducto = $parametros['nombreProducto'];
        $nombreEmpleado = $parametros['nombreEmpleado'];
        $cantidad = $parametros['cantidad'];
        $estado = $parametros["estado"];

        $producto = Producto::obtenerProductoByName($nombreProducto);
        $empleado = Usuario::obtenerUsuarioByName($nombreEmpleado);
        $pedido = Pedido::obtenerPedido($id);
        
        $pedido->producto = $producto;
        $pedido->usuarioAsignado = $empleado;
        $pedido->cantidad = $cantidad;
        $pedido->estado = $estado;

        Pedido::modificarPedido($pedido);

        $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['pedido'];
        $pedido = Pedido::obtenerPedido($id);
        Pedido::borrarPedido($pedido);

        $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>