<?php

include_once "./Models/Pedido.php";
include_once "./Models/Mesa.php";
include_once "./Models/Usuario.php";


class Comanda{

    public $id_comanda;
    public $mesa;
    public $arrayPedidos;
    public $mozo;
    public $fechaComanda;

    public function crearComanda()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO comandas (id_mesa,id_mozo,fecha_comanda) VALUES (?,?,?)");
        $fechaString = date_format($this->fechaComanda, 'Y-m-d H:i:s');

        $consulta->bindParam(1, $this->mesa->_idMesa);
        $consulta->bindParam(2, $this->mozo->_idUsuario);
        $consulta->bindParam(3, $fechaString);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM comandas");
        $consulta->execute();
        // $consulta->fetchAll(PDO::FETCH_OBJ);
        // return $consulta->fetchAll(PDO::FETCH_CLASS, 'Comanda');
        $arrayComandas = array();
        foreach($consulta->fetchAll(PDO::FETCH_OBJ) as $prototipo)
        {
            array_push($arrayComandas,Comanda::transformarPrototipo($prototipo));
        }
        

        return $arrayComandas;
    }

    public static function obtenerComanda($id)
    {
        $rtn = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM comandas WHERE id_comanda = ?");
        $consulta->bindParam(1, $id);
        $consulta->execute();

        // return $consulta->fetchObject('Comanda');
        // return $consulta->fetch(PDO::FETCH_OBJ);
        $prototipeObject = $consulta->fetch(PDO::FETCH_OBJ);
        if($prototipeObject != false)
        {
            $rtn = Comanda::transformarPrototipo($prototipeObject);
        }

        return $rtn;
    }

    private static function transformarPrototipo($prototipo)
    {   
        $comanda = new Comanda();
        $comanda->id_comanda = $prototipo->id_comanda;
        $comanda->mozo = Usuario::obtenerUsuario($prototipo->id_mozo);
        $comanda->arrayPedidos = Pedido::obtenerTodos($prototipo->id_comanda);
        $comanda->mesa = Mesa::obtenerMesa($prototipo->id_mesa);
        $comanda->fechaComanda = DateTime::createFromFormat('Y-m-d H:i:s',$prototipo->fecha_comanda,new DateTimeZone("America/Argentina/Buenos_Aires"));

        return $comanda;

    }

    public static function modificarComanda($comanda)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE comandas SET id_mozo = ?, id_mesa = ? WHERE id_comanda = ?");

        
        $consulta->bindParam(1, $comanda->mozo->_idUsuario);
        $consulta->bindParam(2, $comanda->mesa->_idMesa);
        $consulta->bindParam(3, $comanda->id_comanda);

        $consulta->execute();
    }

    public static function borrarComanda($comanda)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE comandas WHERE id_comanda = ?");
        $consulta->bindParam(1, $comanda->id_comanda);
        foreach($comanda->arrayPedidos as $pedido)
        {
            Pedido::borrarPedido($pedido);
        }

        $consulta->execute();
    }
}
?>