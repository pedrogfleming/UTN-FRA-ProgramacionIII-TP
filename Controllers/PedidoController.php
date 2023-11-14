<?php

use Illuminate\Support\Facades\Process;
use Slim\Exception\HttpBadRequestException;

include_once MODELS . '/Pedido.php';
include_once MODELS . "/Producto.php";
include_once MODELS . "/Usuario.php";
require_once INTERFACES . '/IApiUsable.php';

class PedidoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();


        $nombreUsuario = $parametros['nombreUsuario'];
        $nombreProducto = $parametros['nombreProducto'];
        $cantidad = $parametros['cantidad'];
        $idMesa = $parametros['idMesa'];
        $nombreCliente = $parametros['nombreCliente'];

        // Creamos el pedido
        $pedido = new Pedido();
        $pedido->usuarioAsignado = Usuario::obtenerUsuarioByName($nombreUsuario);
        if($pedido->usuarioAsignado === false){
            throw new Exception("No existe el usuario con el nombre suministrado");
        }

        $pedido->producto = Producto::obtenerProductoByName($nombreProducto);
        
        if($pedido->producto === false){
            throw new Exception("No existe el producto con el nombre suministrado");
        }
        $pedido->cantidad = (int)$cantidad;
        $pedido->idMesa = (int)$idMesa;
        $pedido->nombreCliente = $nombreCliente;
        $pedido->importeTotal = $pedido->cantidad * $pedido->producto->precio;
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
        $idMesa = $parametros['idMesa'];
        $nombreUsuario = $parametros['nombreUsuario'];
        $nombreProducto = $parametros['nombreProducto'];
        $cantidad = $parametros['cantidad'];
        $nombreCliente = $parametros['nombreCliente'];
        $estado = $parametros["estado"];

        $usuarioAsignado = Usuario::obtenerUsuarioByName($nombreUsuario);
        if($usuarioAsignado === false){
            throw new Exception("No existe el usuario con el nombre suministrado");
        }

        $producto = Producto::obtenerProductoByName($nombreProducto);
        
        if($producto === false){
            throw new Exception("No existe el producto con el nombre suministrado");
        }

        $pedido = Pedido::obtenerPedido($id);
        
        $pedido->producto = $producto;
        $pedido->idMesa = $idMesa;
        $pedido->usuarioAsignado = $usuarioAsignado;
        $pedido->producto = $producto;
        $pedido->cantidad = $cantidad;
        $pedido->importeTotal = $pedido->producto->precio * $pedido->cantidad;
        $pedido->nombreCliente = $nombreCliente;
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