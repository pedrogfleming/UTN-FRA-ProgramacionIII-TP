<?php

use Illuminate\Support\Facades\Process;
use Slim\Exception\HttpBadRequestException;

include_once MODELS . '/Pedido.php';
include_once MODELS . "/Item.php";
include_once MODELS . "/Usuario.php";
require_once INTERFACES . '/IApiUsable.php';

class PedidoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $items = $parametros['items'];

        $pedido = new Pedido();
        $nombreUsuario = $parametros['nombreUsuario'];
        $nombreCliente = $parametros['nombreCliente'];
        $pedido->nombreCliente = $nombreCliente;

        $pedido->usuarioAsignado = Usuario::obtenerUsuarioByName($nombreUsuario);
        if ($pedido->usuarioAsignado === false) {
            throw new Exception("No existe el usuario con el nombre suministrado");
        }
        $idMesa = $parametros['idMesa'];
        $mesaObtenida = Mesa::obtenerMesa($idMesa);
        if ($mesaObtenida === false) {
            throw new Exception("No existe la mesa con el id suministrado");
        }
        $pedido->idMesa = $mesaObtenida->idMesa;
        $pedido->itemsPedidos = array();
        $pedido->fechaCreacion = new DateTime("now", new DateTimeZone("America/Argentina/Buenos_Aires"));
        $minutosAcc = 0;
        foreach ($items as $itemData) {
            $nombreProducto = $itemData['nombreProducto'];
            $cantidad = $itemData['cantidad'];
            $item = new Item();
            $productoObtenido = Producto::obtenerProductoByName($nombreProducto);
            $item->cantidad = (int)$cantidad;
            if ($productoObtenido === false) {
                throw new Exception("No existe el producto con el nombre suministrado");
            }
            $item->idProducto = $productoObtenido->idProducto;
            $item->fechaCreacion = new DateTime("now", new DateTimeZone("America/Argentina/Buenos_Aires"));
            $minutosDelItem = $productoObtenido->tiempoPreparacion * $item->cantidad;
            $minutosAcc += $minutosDelItem;
            $interval = DateInterval::createFromDateString($minutosDelItem . 'minutes');
            $fechaEstimadaFinalizacion = clone $item->fechaCreacion;
            $item->fechaEstimadaFinalizacion = $fechaEstimadaFinalizacion->add($interval);
            // $item->fechaEstimadaFinalizacion = $item->fechaCreacion->add($interval);
            $item->estado = Item::ESTADO_PENDIENTE;
            array_push($pedido->itemsPedidos, $item);
            $pedido->importeTotal += $productoObtenido->precio * $item->cantidad;
        }
        // La suma de todos los minutos de cada uno de los items del pedido
        $aux = DateInterval::createFromDateString($minutosAcc . 'minutes');
        $pedido->fechaEstimadaDeFinalizacion = clone $pedido->fechaCreacion;
        $pedido->fechaEstimadaDeFinalizacion = $pedido->fechaEstimadaDeFinalizacion->add($aux);

        $idCreado = $pedido->crearPedido();
        $mensaje = "Items creados con éxito";
        $nombreFoto = $idCreado . "-pedido.jpg";
        $fotoGuardadaConExito = $this->GuardarFoto($nombreFoto);

        if (!$fotoGuardadaConExito->success) {
            $mensaje =  $mensaje . ". No se pudo guardar la foto del pedido. Error: " . $fotoGuardadaConExito->err;
        }

        $payload = json_encode(array("mensaje" => $mensaje));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args["pedido"];
        $pedido = Pedido::obtenerPedido($id);
        if (!$pedido) {
            $ret = new stdClass();
            $ret->err = "no se encontro el pedido con el id solicitado";
            $err_payload = json_encode($ret);
            $response->getBody()->write($err_payload);
        } else {
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

        $idPedido = $args['pedido'];
        $idMesa = $parametros['idMesa'];
        $nombreUsuario = $parametros['nombreUsuario'];
        $nombreCliente = $parametros['nombreCliente'];
        $estadoPedido = $parametros['estado'];
        $items = $parametros['items'];

        $usuarioAsignado = Usuario::obtenerUsuarioByName($nombreUsuario);
        if ($usuarioAsignado === false) {
            throw new Exception("No existe el usuario con el nombre suministrado");
        }

        $pedido = Pedido::obtenerPedido($idPedido);
        if ($pedido === false) {
            throw new Exception("No existe el pedido con el id suministrado: ");
        }

        Item::borrarItem($idPedido);
        $accImportTotal = 0;
        $minutosAcc = 0;
        $pedido->itemsPedidos = [];
        foreach ($items as $item) {
            $producto = Producto::obtenerProductoByName($item["nombreProducto"]);
            if ($producto === false) {
                throw new Exception("No existe el producto con el nombre suministrado: " . $item["nombreProducto"]);
            }
            $i = new Item();
            $i->idPedido = $idPedido;
            $i->idProducto = $producto->idProducto;
            $i->cantidad = (int)$item["cantidad"];
            $i->estado = $item["estado"];
            $i->fechaCreacion =  new DateTime("now", new DateTimeZone("America/Argentina/Buenos_Aires"));
            $tiempoEnMinutosTotalDelPedido = $producto->tiempoPreparacion * $i->cantidad;
            $minutosAcc += $tiempoEnMinutosTotalDelPedido;
            $interval = DateInterval::createFromDateString($tiempoEnMinutosTotalDelPedido . 'minutes');
            $fechaEstimadaFinalizacion = clone $i->fechaCreacion;
            $item->fechaEstimadaFinalizacion = $fechaEstimadaFinalizacion->add($interval);
            if ($i->crearItem() !== true) {
                throw new Exception("No se pudo modificar el item " . $item["nombreProducto"] . " del pedido " . $idPedido);
            }
            $accImportTotal += $item["cantidad"] * $producto->precio;
            array_push($pedido->itemsPedidos, $i);
        }

        $pedido->importeTotal = $accImportTotal;
        $pedido->usuarioAsignado = $usuarioAsignado;
        $pedido->idMesa = (int)$idMesa;
        $pedido->nombreCliente = $nombreCliente;
        $pedido->estado = $estadoPedido;

        Pedido::modificarPedido($pedido);

        $payload = json_encode(array("mensaje" => "Pedido modificado con éxito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['pedido'];
        $pedido = Pedido::obtenerPedido($id);
        Pedido::borrarPedido($pedido);

        $payload = json_encode(array("mensaje" => "Pedido borrado con éxito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    private function GuardarFoto($nombreArchivo)
    {

        $ret = new stdClass;
        // La carpeta debe crearse previamente
        $carpeta_archivo = '../ImagenesPedidos/';

        // Datos del archivo enviado por POST
        $tipo_archivo =  $_FILES['fotoPedido']['type'];
        $tamano_archivo =  $_FILES['fotoPedido']['size'];

        // Ruta de destino, carpeta + nombre del archivo que quiero guardar
        $ruta_destino = $carpeta_archivo . $nombreArchivo;
        // Realizamos las validaciones del archivo
        if (!((strpos($tipo_archivo, "png") || strpos($tipo_archivo, "jpeg") || strpos($tipo_archivo, "jpg")) && ($tamano_archivo < 300000))) {
            $ret->success = false;
            $ret->err = "La extensión o el tamaño de los archivos no es correcto. <br><br><table><tr><td><li>Solo se permiten archivos .png o .jpg<br><li>Se permiten archivos de un máximo de 300 Kb.</td></tr></table>";
        } else {
            $aux = $_FILES['fotoPedido']['tmp_name'];
            if (move_uploaded_file($_FILES['fotoPedido']['tmp_name'],  $ruta_destino)) {
                $ret->success = true;
            } else {
                $ret->success = false;
                $ret->err = "Se produjo un error al cargar el archivo. No se pudo guardar.";
            }
        }
        return $ret;
    }
}
