<?php
require_once DB . "/AccesoDatos.php";
class Producto
{

    public $idProducto;
    public $titulo;
    public $tiempoPreparacion;
    public $precio;
    public $estado;
    public $sector;
    public $fechaCreacion;


    const estado_ACTIVO = "activo";
    const estado_INACTIVO = "inactivo";
    const sector_CERVEZA = "cerveza";
    const sector_BARTENDER = "bar";
    const sector_COCINA = "cocina";

    public function crearProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (titulo,tiempo_preparacion,precio,estado,sector,fecha_creacion) VALUES (?,?,?,?,?,?)");
        $fecha = new DateTime("now", new DateTimeZone("America/Argentina/Buenos_Aires"));
        $fechaString = date_format($fecha, 'Y-m-d H:i:s');
        $consulta->bindParam(1, $this->titulo);
        $consulta->bindParam(2, $this->tiempoPreparacion);
        $consulta->bindParam(3, $this->precio);
        $consulta->bindParam(4, $this->estado);
        $consulta->bindParam(5, $this->sector);
        $consulta->bindParam(6, $fechaString);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos");
        $consulta->execute();
        // $consulta->fetchAll(PDO::FETCH_OBJ);
        // return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
        $arrayProductos = array();
        foreach ($consulta->fetchAll(PDO::FETCH_OBJ) as $prototipo) {
            array_push($arrayProductos, Producto::transformarPrototipo($prototipo));
        }

        return $arrayProductos;
    }

    public static function obtenerProducto($id)
    {
        $rtn = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos WHERE id_producto = ?");
        $consulta->bindParam(1, $id);
        $consulta->execute();

        $prototipeObject = $consulta->fetch(PDO::FETCH_OBJ);
        if ($prototipeObject != false) {
            $rtn = Producto::transformarPrototipo($prototipeObject);
        }

        return $rtn;
    }

    public static function obtenerProductoByName($nombreProducto)
    {
        $rtn = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos WHERE titulo = ?");
        $consulta->bindParam(1, $nombreProducto);
        $consulta->execute();

        $prototipeObject = $consulta->fetch(PDO::FETCH_OBJ);
        if ($prototipeObject != false) {
            $rtn = Producto::transformarPrototipo($prototipeObject);
        }

        return $rtn;
    }

    private static function transformarPrototipo($prototipo)
    {
        $producto = new Producto();
        $producto->idProducto = $prototipo->id_producto;
        $producto->titulo = $prototipo->titulo;
        $producto->tiempoPreparacion = $prototipo->tiempo_preparacion;
        $producto->fechaCreacion = DateTime::createFromFormat('Y-m-d H:i:s', $prototipo->fecha_creacion, new DateTimeZone("America/Argentina/Buenos_Aires"));
        $producto->precio = $prototipo->precio;
        $producto->estado = $prototipo->estado;
        $producto->sector = $prototipo->sector;
        return $producto;
    }

    public static function modificarProducto($producto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET titulo = ?, tiempo_preparacion = ?, precio = ?, estado = ?, sector = ?  WHERE id_producto = ?");
        $consulta->bindParam(1, $producto->titulo);
        $consulta->bindParam(2, $producto->tiempoPreparacion);
        $consulta->bindParam(3, $producto->precio);
        $consulta->bindParam(4, $producto->estado);
        $consulta->bindParam(5, $producto->sector);
        $consulta->bindParam(6, $producto->idProducto);
        $consulta->execute();
    }

    public static function borrarProducto($producto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM productos WHERE id_producto = ?");
        $consulta->bindParam(1, $producto->idProducto);
        $consulta->execute();
    }
}
