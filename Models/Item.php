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
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ItemPedidos");
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
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ItemPedidos WHERE id_pedido = ?");
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
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ItemPedidos WHERE id_pedido = ? AND id_producto = ?");
        $consulta->bindParam(1, $idPedido);
        $consulta->bindParam(2, $idProducto);
        $consulta->execute();

        $prototipoObject = $consulta->fetch(PDO::FETCH_OBJ);
        if ($prototipoObject != false) {
            $rtn = Item::transformarPrototipo($prototipoObject);
        }

        return $rtn;
    }

    private static function transformarPrototipo($prototipo)
    {
        $item = new Item();
        $item->idPedido = $prototipo->id_pedido;
        $item->idProducto = $prototipo->id_producto;
        $item->cantidad = (int)$prototipo->cantidad;
        $item->fechaCreacion = $prototipo->fecha_creacion;
        $item->fechaEstimadaFinalizacion = $prototipo->fecha_estimada_finalizacion;
        $item->fechaFinalizacion = $prototipo->fecha_finalizacion;
        $item->estado = $prototipo->estado;
        return $item;
    }

    public static function modificarItem($item)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE ItemPedidos SET cantidad = ?, SET fecha_creacion = ? ,  fecha_estimada_finalizacion = ?, fecha_finalizacion = ?, estado = ?  WHERE id_pedido = ? AND id_producto = ?");
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
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM itempedidos WHERE id_pedido = ? and ISNULL(id_producto = ?)");
        $consulta->bindParam(1, $idPedido);
        $consulta->bindParam(2, $idProducto);
        $consulta->execute();
    }
}
