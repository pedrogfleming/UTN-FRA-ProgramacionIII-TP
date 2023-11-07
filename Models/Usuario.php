<?php
require_once DB . "/AccesoDatos.php";

class Usuario{

    public $idUsuario;
    public $nombre;
    public $fechaCreacion;
    public $fechaFinalizacion;
    public $user;
    public $password;
    public $sector;
    public $tipo;

    const TIPO_BARTENDER = "bartender";
    const TIPO_CERVEZERO = "cervecero";
    const TIPO_MOZO = "mozo";
    const TIPO_COCINERO = "cocinero";
    const TIPO_SOCIO = "socio";

    const SECTOR_COCINA = "cocina";
    const SECTOR_CERVECERIA = "cerveza";
    const SECTOR_MESAS = "mesas";
    const SECTOR_ADMINISTRACION = "adminisracion";
    const SECTOR_BAR = "bar";

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (nombre, fechaCreacion,user,password,sector,tipo) VALUES (?,?,?,?,?,?)");
        $fecha = new DateTime("now",new DateTimeZone("America/Argentina/Buenos_Aires"));
        $claveHash = password_hash($this->password, PASSWORD_DEFAULT);
        $consulta->bindParam(1, $this->nombre);
        $consulta->bindParam(2, date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->bindParam(3, $this->user);
        $consulta->bindParam(4, $claveHash);
        $consulta->bindParam(5, $this->sector);
        $consulta->bindParam(6, $this->tipo);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios");
        $consulta->execute();

        $arrayUsuarios = array();
        foreach($consulta->fetchAll(PDO::FETCH_OBJ) as $prototipo)
        {
            array_push($arrayUsuarios,Usuario::transformarPrototipo($prototipo));
        }
        

        return $arrayUsuarios;
    }

    public static function obtenerUsuario($id)
    {
        $rtn = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE idUsuario = ?");
        $consulta->bindParam(1, $id);
        $consulta->execute();

        $prototipeObject = $consulta->fetch(PDO::FETCH_OBJ);
        if($prototipeObject != false)
        {
            $rtn = Usuario::transformarPrototipo($prototipeObject);
        }

        return $rtn;
    }

    public static function obtenerUsuarioByName($nombreUsuario)
    {
        $rtn = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE nombre = ?");
        $consulta->bindParam(1, $nombreUsuario);
        $consulta->execute();

        $prototipeObject = $consulta->fetch(PDO::FETCH_OBJ);
        if($prototipeObject != false)
        {
            $rtn = Usuario::transformarPrototipo($prototipeObject);
        }

        return $rtn;
    }

    private static function transformarPrototipo($prototipo)
    {   
        $usuario = new Usuario();
        $usuario->idUsuario = $prototipo->idUsuario;
        $usuario->nombre = $prototipo->nombre;
        $usuario->fechaCreacion = DateTime::createFromFormat('Y-m-d H:i:s',$prototipo->fechaCreacion,new DateTimeZone("America/Argentina/Buenos_Aires"));
        if($prototipo->fechaFinalizacion != NULL)
        {
            $usuario->fechaFinalizacion = DateTime::createFromFormat('Y-m-d H:i:s',$prototipo->fechaFinalizacion,new DateTimeZone("America/Argentina/Buenos_Aires"));
        }
        else{
            $usuario->fechaFinalizacion = $prototipo->fechaFinalizacion;
        }
        $usuario->user = $prototipo->user;
        $usuario->password = $prototipo->password;
        $usuario->sector = $prototipo->sector;
        $usuario->tipo = $prototipo->tipo;
        return $usuario;
    }

    public static function modificarUsuario($usuario)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET nombre = ?, user = ?, password = ?, sector = ?, tipo = ?  WHERE idUsuario = ?");
        $claveHash = password_hash($usuario->password, PASSWORD_DEFAULT);
        $consulta->bindParam(1, $usuario->nombre);
        $consulta->bindParam(2, $usuario->user);
        $consulta->bindParam(3, $claveHash);
        $consulta->bindParam(4, $usuario->sector);
        $consulta->bindParam(5, $usuario->tipo);
        $consulta->bindParam(6, $usuario->idUsuario);
        $consulta->execute();
    }

    public static function borrarUsuario($usuario)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fechaFinalizacion = ? WHERE idUsuario = ?");
        $fecha = new DateTime("now",new DateTimeZone("America/Argentina/Buenos_Aires"));
        $fechaString = date_format($fecha, 'Y-m-d H:i:s');
        $consulta->bindParam(1, $fechaString);
        $consulta->bindParam(2, $usuario->idUsuario);
        $consulta->execute();
    }
}

?>