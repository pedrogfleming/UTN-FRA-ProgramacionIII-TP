<?php
ECHO __DIR__ ;

include_once "./Models/Comanda.php";
include_once "Producto.php";
include_once "Usuario.php";

require_once DB . "/AccesoDatos.php";

class Pedido{

    public $idPedido;
    public $comanda;
    public $usuarioAsignado;
    public $producto;
    public $cantidad;
    public $fechaEstimadaDeFinalizacion;
    public $fechaFinalizacion;
    public $sector;
    public $estado;
    
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
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (id_comanda,id_usuario,id_producto,cantidad,fecha_estimada_finalizacion,sector,estado) VALUES (?,?,?,?,?,?,?)");
        
        // Calulo la fecha de finalizacion estimada del pedido
        $tiempoEnMinutosDeProducto = $this->cantidad*$this->producto->_tiempoPreparacion;
        $interval = DateInterval::createFromDateString($tiempoEnMinutosDeProducto.'minutes');
        $fechaEstimadaFinalizacion = $this->comanda->fechaComanda->add($interval);

        // Se cargan los datos faltantes en el pedido
        $this->fechaEstimadaDeFinalizacion = $fechaEstimadaFinalizacion;
        $this->sector = $this->producto->sector;
        $this->estado = self::ESTADO_PENDIENTE;

        $fechaEstimadaFinalizacionString = date_format($this->fechaEstimadaDeFinalizacion, 'Y-m-d H:i:s');

        
        $consulta->bindParam(1, $this->comanda->id_comanda);
        $consulta->bindParam(2, $this->usuarioAsignado->_idUsuario);
        $consulta->bindParam(3, $this->producto->_idProducto);
        $consulta->bindParam(4, $this->cantidad);
        $consulta->bindParam(5, $fechaEstimadaFinalizacionString);
        $consulta->bindParam(6, $this->sector);
        $consulta->bindParam(7, $this->estado);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos($idComanda = NULL)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        if($idComanda == null)
        {
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
        }
        else{

            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id_comanda = ?");
            $consulta->bindParam(1, $idComanda);
        }
        
        $consulta->execute();
        // $consulta->fetchAll(PDO::FETCH_OBJ);
        // return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
        $arrayPedidos = array();
        foreach($consulta->fetchAll(PDO::FETCH_OBJ) as $prototipo)
        {
            array_push($arrayPedidos,Pedido::transformarPrototipo($prototipo));
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

        // return $consulta->fetchObject('Pedido');
        // return $consulta->fetch(PDO::FETCH_OBJ);
        $prototipeObject = $consulta->fetch(PDO::FETCH_OBJ);
        if($prototipeObject != false)
        {
            $rtn = Pedido::transformarPrototipo($prototipeObject);
        }

        return $rtn;
    }

    private static function transformarPrototipo($prototipo)
    {   
        $pedido = new Pedido();
        $pedido->idPedido = $prototipo->id_pedido;
        $pedido->comanda = Comanda::obtenerComanda($prototipo->id_comanda);
        $pedido->usuarioAsignado = Usuario::obtenerUsuario($prototipo->id_usuario);
        $pedido->producto = Producto::obtenerProducto($prototipo->id_producto);
        $pedido->cantidad = $prototipo->cantidad;
        $pedido->fechaEstimadaDeFinalizacion = DateTime::createFromFormat('Y-m-d H:i:s',$prototipo->fecha_estimada_finalizacion,new DateTimeZone("America/Argentina/Buenos_Aires"));
        if($prototipo->FECHA_FINALIZACION != NULL)
        {
            $pedido->fechaFinalizacion = DateTime::createFromFormat('Y-m-d H:i:s',$prototipo->FECHA_FINALIZACION,new DateTimeZone("America/Argentina/Buenos_Aires"));

        }
        else{
            $pedido->fechaFinalizacion = $prototipo->FECHA_BAJA;
        }
        $pedido->sector = $prototipo->sector;
        $pedido->estado = $prototipo->estado;
        return $pedido;

    }

    public static function modificarPedido($pedido)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET id_usuario = ?, id_producto = ?, cantidad = ?, fecha_estimada_finalizacion = ?, sector = ?, estado = ? WHERE id_pedido = ?");
        // Calulo la fecha de finalizacion estimada del pedido
        $tiempoEnMinutosDeProducto = $pedido->cantidad*$pedido->producto->_tiempoPreparacion;
        $interval = DateInterval::createFromDateString($tiempoEnMinutosDeProducto.'minutes');
        $fechaEstimadaFinalizacion = $pedido->comanda->fechaComanda->add($interval);
        $fechaEstimadaFinalizacionString = date_format($fechaEstimadaFinalizacion, 'Y-m-d H:i:s');
        
        $consulta->bindParam(1, $pedido->usuarioAsignado->_idUsuario);
        $consulta->bindParam(2, $pedido->producto->_idProducto);
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

?>