<?php
include_once "Producto.php";
include_once "Usuario.php";

require_once DB . "/AccesoDatos.php";

class Pedido
{

    public $idPedido;
    public $idMesa;
    public $usuarioAsignado;
    public $itemsPedidos;
    public $fechaCreacion;
    public $fechaEstimadaDeFinalizacion;
    public $fechaFinalizacion;
    public $estado;
    public $importeTotal;
    public $nombreCliente;
    public $minutosEstimados;

    const ESTADO_PENDIENTE = "pendiente";
    const ESTADO_PREPARACION = "en preparación";
    const ESTADO_LISTO = "listo para servir";
    const ESTADO_CANCELADO = "cancelado";
    const ESTADO_PAGADO = "pagado";

    const SECTOR_CERVEZA = "cerveza";
    const SECTOR_BARTENDER = "bartender";
    const SECTOR_COCINA = "cocina";

    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (id_usuario, id_mesa, fecha_creacion, fecha_estimada_finalizacion, importe_total, nombre_cliente, estado) VALUES (?,?,?,?,?,?,?)");
        $this->estado = self::ESTADO_PENDIENTE;

        $fechaEstimadaFinalizacionString = date_format($this->fechaEstimadaDeFinalizacion, 'Y-m-d H:i:s');
        $fechaCreacionString = date_format($this->fechaCreacion, 'Y-m-d H:i:s');

        $consulta->bindParam(1, $this->usuarioAsignado->idUsuario);
        $consulta->bindParam(2, $this->idMesa);
        $consulta->bindParam(3, $fechaCreacionString);
        $consulta->bindParam(4, $fechaEstimadaFinalizacionString);
        $consulta->bindParam(5, $this->importeTotal);
        $consulta->bindParam(6, $this->nombreCliente);
        $consulta->bindParam(7, $this->estado);
        $consulta->execute();

        $idPedido = $objAccesoDatos->obtenerUltimoId();
        //Registro cada uno de los item del pedido en la tabla correspondiente
        foreach ($this->itemsPedidos as $item) {
            $item->idPedido = $idPedido;
            if (!$item->crearItem()) {
                throw new Exception("No se pudo crear el item " . $item->idProducto . " del pedido " . $item->pedido);
            }
        }
        return $idPedido;
    }

    public static function obtenerTodos($filtroEstado = null, $traerItems = null)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE eliminado = " . ACTIVO . " AND (? IS NULL OR estado = ?)");
        $consulta->bindParam(1, $filtroEstado);
        $consulta->bindParam(2, $filtroEstado);
        $consulta->execute();

        $arrayPedidos = array();
        foreach ($consulta->fetchAll(PDO::FETCH_OBJ) as $prototipo) {
            array_push($arrayPedidos, Pedido::transformarPrototipo($prototipo, $traerItems));
        }

        return $arrayPedidos;
    }

    public static function obtenerPedido($id, $traerItems = null)
    {
        $rtn = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id_pedido = ? AND eliminado = " . ACTIVO);
        $consulta->bindParam(1, $id);
        $consulta->execute();

        $prototipeObject = $consulta->fetch(PDO::FETCH_OBJ);
        if ($prototipeObject != false) {
            $rtn = Pedido::transformarPrototipo($prototipeObject, $traerItems);
        }

        return $rtn;
    }

    private static function transformarPrototipo($prototipo, $traerItems = null)
    {
        $pedido = new Pedido();
        $pedido->idPedido = $prototipo->id_pedido;
        $pedido->idMesa = $prototipo->id_mesa;
        $pedido->usuarioAsignado = $prototipo->id_usuario;
        $pedido->fechaCreacion = DateTime::createFromFormat('Y-m-d H:i:s', $prototipo->fecha_creacion, new DateTimeZone("America/Argentina/Buenos_Aires"));
        $pedido->fechaEstimadaDeFinalizacion = DateTime::createFromFormat('Y-m-d H:i:s', $prototipo->fecha_estimada_finalizacion, new DateTimeZone("America/Argentina/Buenos_Aires"));

        if ($prototipo->fecha_finalizacion != NULL) {
            $pedido->fechaFinalizacion = DateTime::createFromFormat('Y-m-d H:i:s', $prototipo->fecha_finalizacion, new DateTimeZone("America/Argentina/Buenos_Aires"));
        } else {
            $pedido->fechaFinalizacion = $prototipo->fecha_finalizacion;
        }

        $pedido->importeTotal = (float) $prototipo->importe_total;
        $pedido->nombreCliente = $prototipo->nombre_cliente;
        $pedido->estado = $prototipo->estado;

        $pedido->itemsPedidos = $traerItems == "true" ? Item::obtenerItemsPorPedido($pedido->idPedido) : [];
        // Calcular la diferencia en minutos entre la fecha de creación y la fecha estimada de finalización
        $diferencia = $pedido->fechaCreacion->diff($pedido->fechaEstimadaDeFinalizacion);
        $pedido->minutosEstimados = $diferencia->days * 24 * 60 + $diferencia->h * 60 + $diferencia->i;
        return $pedido;
    }

    public static function modificarPedido($pedido)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();

        if($pedido->estado == Pedido::ESTADO_PAGADO || $pedido->fechaFinalizacion){
            $pedido->fechaFinalizacion = new DateTime("now", new DateTimeZone("America/Argentina/Buenos_Aires"));
            $pedido->fechaFinalizacion = date_format($pedido->fechaFinalizacion, 'Y-m-d H:i:s');
        }
        $fechaEstimadaFinalizacionString = date_format($pedido->fechaEstimadaDeFinalizacion, 'Y-m-d H:i:s');
        $fechaCreacionString = date_format($pedido->fechaCreacion, 'Y-m-d H:i:s');

        // Actualizar el pedido en la base de datos
        $consulta = $objAccesoDato->prepararConsulta("UPDATE Pedidos SET id_usuario = ?, id_mesa = ?, fecha_creacion = ?, fecha_estimada_finalizacion = ?, fecha_finalizacion = ?, importe_total = ?, nombre_cliente = ?, estado = ? WHERE id_pedido = ? AND eliminado = " . ACTIVO);
        
        $consulta->bindParam(1, $pedido->usuarioAsignado->idUsuario);
        $consulta->bindParam(2, $pedido->idMesa);
        $consulta->bindParam(3, $fechaCreacionString);
        $consulta->bindParam(4, $fechaEstimadaFinalizacionString);
        $consulta->bindParam(5, $pedido->fechaFinalizacion);
        $consulta->bindParam(6, $pedido->importeTotal);
        $consulta->bindParam(7, $pedido->nombreCliente);
        $consulta->bindParam(8, $pedido->estado);
        $consulta->bindParam(9, $pedido->idPedido);

        $consulta->execute();
    }

    public static function modificarItemsPedido($pedido, $itemsModificados){
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        try {
            $objAccesoDato->begin_transaction();
            // Item::borrarItem($pedido->idPedido);

            // Eliminamos todos los items del pedido y volvemos a insertar en base a los recibidos
            $limpiarItems = $objAccesoDato->prepararConsulta("DELETE FROM itempedidos WHERE id_pedido = ?");
            $limpiarItems->bindParam(1, $pedido->idPedido);
            $limpiarItems->execute();

            $accImportTotal = 0;
            $minutosAcc = 0;
            $pedido->itemsPedidos = [];

            foreach ($itemsModificados as $item) {
                $producto = Producto::obtenerProductoByName($item["nombreProducto"]);
                if ($producto === false) {
                    throw new Exception("No existe el producto con el nombre suministrado: " . $item["nombreProducto"]);
                }
                $i = new Item();
                $i->idPedido = $pedido->idPedido;
                $i->idProducto = $producto->idProducto;
                $i->cantidad = (int)$item["cantidad"];
                $i->estado = $item["estado"];
                $i->fechaCreacion =  new DateTime("now", new DateTimeZone("America/Argentina/Buenos_Aires"));
                $tiempoEnMinutosTotalDelPedido = $producto->tiempoPreparacion * $i->cantidad;
                $minutosAcc += $tiempoEnMinutosTotalDelPedido;
                $interval = DateInterval::createFromDateString($tiempoEnMinutosTotalDelPedido . 'minutes');
                $fechaEstimadaFinalizacion = clone $i->fechaCreacion;
                $i->fechaEstimadaFinalizacion = $fechaEstimadaFinalizacion->add($interval);

                if ($i->crearItem() !== true) {
                    throw new Exception("No se pudo modificar el item " . $item["nombreProducto"] . " del pedido " . $pedido->idPedido);
                }
                $accImportTotal += $item["cantidad"] * $producto->precio;
                array_push($pedido->itemsPedidos, $i);
            }
            $pedido->importeTotal = $accImportTotal;
            $objAccesoDato->commit_transaction();
        } catch (\Throwable $th) {
            $objAccesoDato->rollback_transaction();
            throw new Exception("Error al intentar actualizar items del pedido: " . $th->getMessage());
        }

    }
    public static function borrarPedido($pedido)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE Pedidos SET estado = ? WHERE id_pedido = ? AND eliminado = " . ACTIVO);
        $consulta->bindParam(1, self::ESTADO_CANCELADO);
        $consulta->bindParam(2, $pedido->idPedido);
        $consulta->execute();
    }
}
