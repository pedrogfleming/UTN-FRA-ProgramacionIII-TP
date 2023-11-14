<?php
include_once "Producto.php";
include_once "Usuario.php";

require_once DB . "/AccesoDatos.php";

class Pedido
{

    public $idPedido;
    public $idMesa;
    public $usuarioAsignado;
    public $producto;
    public $cantidad;
    public $fechaCreacion;
    public $fechaEstimadaDeFinalizacion;
    public $fechaFinalizacion;
    public $sector;
    public $estado;

    public $importeTotal;
    public $nombreCliente;
    
    const ESTADO_PENDIENTE = "pendiente";
    const ESTADO_PREPARACION = "en preparación";
    const ESTADO_LISTO = "listo para servir";
    const ESTADO_CANCELADO = "cancelado";

    const SECTOR_CERVEZA = "cerveza";
    const SECTOR_BARTENDER = "bartender";
    const SECTOR_COCINA = "cocina";

    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (id_usuario, id_producto, id_mesa, cantidad,fecha_creacion, fecha_estimada_finalizacion, sector, importe_total, nombre_cliente,estado) VALUES (?,?,?,?,?,?,?,?,?,?)");

        $this->fechaCreacion = new Datetime('now');
        // Calulo la fecha de finalizacion estimada del pedido
        $tiempoEnMinutosDeProducto = $this->cantidad * $this->producto->tiempoPreparacion;
        $interval = DateInterval::createFromDateString($tiempoEnMinutosDeProducto . 'minutes');
        $fechaEstimadaFinalizacion = $this->fechaCreacion->add($interval);

        // Se cargan los datos faltantes en el pedido
        $this->fechaEstimadaDeFinalizacion = $fechaEstimadaFinalizacion;
        $this->sector = $this->producto->sector;
        $this->estado = self::ESTADO_PENDIENTE;

        $fechaEstimadaFinalizacionString = date_format($this->fechaEstimadaDeFinalizacion, 'Y-m-d H:i:s');
        $fechaCreacionString = date_format($this->fechaCreacion, 'Y-m-d H:i:s');

        $consulta->bindParam(1, $this->usuarioAsignado->idUsuario);
        $consulta->bindParam(2, $this->producto->idProducto);
        $consulta->bindParam(3, $this->idMesa);
        $consulta->bindParam(4, $this->cantidad);
        $consulta->bindParam(5, $fechaCreacionString);
        $consulta->bindParam(6, $fechaEstimadaFinalizacionString);
        $consulta->bindParam(7, $this->sector);
        $consulta->bindParam(8, $this->importeTotal);
        $consulta->bindParam(9, $this->nombreCliente);
        $consulta->bindParam(10, $this->estado);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos($idPedido = NULL)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        if ($idPedido == null) {
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
        } else {

            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE idPedido = ?");
            $consulta->bindParam(1, $idPedido);
        }

        $consulta->execute();

        $arrayPedidos = array();
        foreach ($consulta->fetchAll(PDO::FETCH_OBJ) as $prototipo) {
            array_push($arrayPedidos, Pedido::transformarPrototipo($prototipo));
        }

        return $arrayPedidos;
    }

    public static function obtenerPedido($id)
    {
        $rtn = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id_pedido = ?");
        $consulta->bindParam(1, $id);
        $consulta->execute();

        $prototipeObject = $consulta->fetch(PDO::FETCH_OBJ);
        if ($prototipeObject != false) {
            $rtn = Pedido::transformarPrototipo($prototipeObject);
        }

        return $rtn;
    }

    private static function transformarPrototipo($prototipo)
    {
        $pedido = new Pedido();
        $pedido->idPedido = $prototipo->id_pedido;
        $pedido->usuarioAsignado = $prototipo->id_usuario;
        $pedido->producto = $prototipo->id_producto;
        $pedido->cantidad = $prototipo->cantidad;
        $pedido->idMesa = $prototipo->id_mesa;
        $pedido->fechaCreacion = $prototipo->fecha_creacion;
        $pedido->fechaEstimadaDeFinalizacion = DateTime::createFromFormat('Y-m-d H:i:s', $prototipo->fecha_estimada_finalizacion, new DateTimeZone("America/Argentina/Buenos_Aires"));
        if ($prototipo->fecha_finalizacion != NULL) {
            $pedido->fechaFinalizacion = DateTime::createFromFormat('Y-m-d H:i:s', $prototipo->fecha_finalizacion, new DateTimeZone("America/Argentina/Buenos_Aires"));
        } else {
            $pedido->fechaFinalizacion = $prototipo->fecha_finalizacion;
        }
        $pedido->sector = $prototipo->sector;
        $pedido->importeTotal = $prototipo->importe_total;
        $pedido->nombreCliente = $prototipo->nombre_cliente;
        $pedido->estado = $prototipo->estado;
        return $pedido;
    }

    public static function modificarPedido($pedido)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET id_usuario = ?, id_producto = ?, cantidad = ?, fecha_estimada_finalizacion = ?, sector = ?, estado = ? WHERE id_pedido = ?");
        // Calulo la fecha de finalizacion estimada del pedido
        $tiempoEnMinutosDeProducto = $pedido->cantidad * $pedido->producto->tiempoPreparacion;
        $interval = DateInterval::createFromDateString($tiempoEnMinutosDeProducto . 'minutes');
        $fechaEstimadaFinalizacion = $pedido->comanda->fechaComanda->add($interval);
        $fechaEstimadaFinalizacionString = date_format($fechaEstimadaFinalizacion, 'Y-m-d H:i:s');

        $consulta->bindParam(1, $pedido->usuarioAsignado->idUsuario);
        $consulta->bindParam(2, $pedido->producto->idProducto);
        $consulta->bindParam(3, $pedido->cantidad);
        $consulta->bindParam(4, $fechaEstimadaFinalizacionString);
        $consulta->bindParam(5, $pedido->producto->sector);
        $consulta->bindParam(6, $pedido->estado);

        $consulta->execute();
    }

    public static function borrarPedido($pedido)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
        $consulta->bindParam(1, self::ESTADO_CANCELADO);
        $consulta->bindParam(2, $pedido->idPedido);
        $consulta->execute();
    }
}
