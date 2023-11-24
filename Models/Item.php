<?php
require_once DB . "/AccesoDatos.php";

class Item
{
    public $idPedido;
    public $idProducto;
    public $cantidad;
    public $fechaCreacion;
    public $fechaEstimadaFinalizacion;
    public $fechaFinalizacion;
    public $estado;
    public $minutosEstimados;

    const ESTADO_PENDIENTE = "pendiente";
    const ESTADO_EN_PREPARACION = "en preparación";
    const ESTADO_LISTO_PARA_SERVIR = "listo para servir";
    const ESTADO_CANCELADO = "cancelado";

    public function crearItem()
    {
        $fechaCreacionString = date_format($this->fechaCreacion, 'Y-m-d H:i:s');
        $fechaEstimadaFinalizacionString = date_format($this->fechaEstimadaFinalizacion, 'Y-m-d H:i:s');

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ItemPedidos (id_pedido, id_producto, cantidad, fecha_creacion, fecha_estimada_finalizacion, fecha_finalizacion, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $consulta->bindParam(1, $this->idPedido);
        $consulta->bindParam(2, $this->idProducto);
        $consulta->bindParam(3, $this->cantidad);
        $consulta->bindParam(4, $fechaCreacionString);
        $consulta->bindParam(5, $fechaEstimadaFinalizacionString);
        $consulta->bindParam(6, $this->fechaFinalizacion);
        $consulta->bindParam(7, $this->estado);
        $consulta->execute();

        return true;
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ItemPedidos AND eliminado = " . ACTIVO);
        $consulta->execute();

        $arrayItems = array();
        foreach ($consulta->fetchAll(PDO::FETCH_OBJ) as $prototipo) {
            array_push($arrayItems, Item::transformarPrototipo($prototipo));
        }

        return $arrayItems;
    }

    public static function obtenerItemsPorPedido($idPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ItemPedidos WHERE id_pedido = ? AND eliminado = " . ACTIVO);
        $consulta->bindParam(1, $idPedido);
        $consulta->execute();

        $arrayItems = array();
        foreach ($consulta->fetchAll(PDO::FETCH_OBJ) as $prototipo) {
            array_push($arrayItems, Item::transformarPrototipo($prototipo));
        }

        return $arrayItems;
    }
    public static function obtenerItem($idPedido, $idProducto)
    {
        $rtn = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ItemPedidos WHERE id_pedido = ? AND id_producto = ? AND eliminado = " . ACTIVO);
        $consulta->bindParam(1, $idPedido);
        $consulta->bindParam(2, $idProducto);
        $consulta->execute();

        $prototipoObject = $consulta->fetch(PDO::FETCH_OBJ);
        if ($prototipoObject != false) {
            $rtn = Item::transformarPrototipo($prototipoObject);
        }

        return $rtn;
    }

    public static function transformarPrototipo($prototipo)
    {
        $item = new Item();
        $item->idPedido = $prototipo->id_pedido;
        $item->idProducto = $prototipo->id_producto;
        $item->cantidad = (int)$prototipo->cantidad;
    
        $item->fechaCreacion = DateTime::createFromFormat('Y-m-d H:i:s', $prototipo->fecha_creacion, new DateTimeZone("America/Argentina/Buenos_Aires"));
        $item->fechaEstimadaFinalizacion = DateTime::createFromFormat('Y-m-d H:i:s', $prototipo->fecha_estimada_finalizacion, new DateTimeZone("America/Argentina/Buenos_Aires"));
    
        $item->fechaFinalizacion = $prototipo->fecha_finalizacion;
        $item->estado = $prototipo->estado;
    
        if (is_string($item->fechaFinalizacion)) {
            $item->fechaFinalizacion = DateTime::createFromFormat('Y-m-d H:i:s', $item->fechaFinalizacion, new DateTimeZone("America/Argentina/Buenos_Aires"));
        }
    
        $diferencia = $item->fechaCreacion->diff($item->fechaEstimadaFinalizacion);
        $item->minutosEstimados = $diferencia->days * 24 * 60 + $diferencia->h * 60 + $diferencia->i;
    
        return $item;
    }

    public static function modificarItem($item)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE ItemPedidos SET cantidad = ?, SET fecha_creacion = ? ,  fecha_estimada_finalizacion = ?, fecha_finalizacion = ?, estado = ?  WHERE id_pedido = ? AND id_producto = ? AND eliminado = " . ACTIVO);
        $consulta->bindParam(1, $item->cantidad);
        $consulta->bindParam(2, $item->fechaCreacion);
        $consulta->bindParam(3, $item->fechaEstimadaFinalizacion);
        $consulta->bindParam(4, $item->fechaFinalizacion);
        $consulta->bindParam(5, $item->estado);
        $consulta->bindParam(6, $item->idPedido);
        $consulta->bindParam(7, $item->idProducto);
        $consulta->execute();
    }

    public static function borrarItem($idPedido, $idProducto = null)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consultaBase = "UPDATE itempedidos SET eliminado = " . INACTIVO . " WHERE id_pedido = ?";
        
        // Añadir condicion para id_producto si está presente
        $consulta = $objAccesoDato->prepararConsulta(
            $idProducto ? $consultaBase . " AND id_producto = ?" : $consultaBase
        );
    
        $consulta->bindParam(1, $idPedido);
        
        if ($idProducto) {
            $consulta->bindParam(2, $idProducto);
        }
    
        $consulta->execute();
    }
    
}
