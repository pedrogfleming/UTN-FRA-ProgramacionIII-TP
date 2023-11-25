<?php
require_once DB . "/AccesoDatos.php";

class Encuesta
{
    public $idEncuesta;
    public $idPedido;
    public $idMesa;
    public $idMozo;
    public $idCocinero;
    public $puntuacionMesa;
    public $puntuacionRestaurante;
    public $puntuacionMozo;
    public $puntuacionCocinero;
    public $experienciaTexto;
    public $fechaCreacion;

    public function guardarEncuesta()
    {
        
        $fechaCreacionString = date_format($this->fechaCreacion, 'Y-m-d H:i:s');

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO Encuestas (id_mesa, id_pedido, id_mozo, id_cocinero, puntuacion_mesa, puntuacion_restaurante, puntuacion_mozo, puntuacion_cocinero, experiencia_texto, fecha_creacion) VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $consulta->bindParam(1, $this->idMesa);
        $consulta->bindParam(2, $this->idPedido);
        $consulta->bindParam(3, $this->idMozo);
        $consulta->bindParam(4, $this->idCocinero);
        $consulta->bindParam(5, $this->puntuacionMesa);
        $consulta->bindParam(6, $this->puntuacionRestaurante);
        $consulta->bindParam(7, $this->puntuacionMozo);
        $consulta->bindParam(8, $this->puntuacionCocinero);
        $consulta->bindParam(9, $this->experienciaTexto);
        $consulta->bindParam(10, $fechaCreacionString);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerEncuesta($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM Encuestas WHERE id_encuesta = ? AND eliminado = " . ACTIVO);
        $consulta->bindParam(1, $id);
        $consulta->execute();

        $encuesta = $consulta->fetch(PDO::FETCH_OBJ);
        if ($encuesta != false) {
            return self::transformarEncuesta($encuesta);
        }

        return null;
    }

    public static function obtenerTodos($idMesa = null, $idMozo = null, $idCocinero = null, $puntuacionMesa = null, $puntuacionRestaurante = null, $puntuacionMozo = null)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consultaStr = "SELECT * FROM Encuestas WHERE eliminado = " . ACTIVO;
    
        // Agrega los filtros a la consulta SQL
        if ($idMesa) {
            $consultaStr .= " AND id_mesa = :idMesa";
        }
        if ($idMozo) {
            $consultaStr .= " AND id_mozo = :idMozo";
        }
        if ($idCocinero) {
            $consultaStr .= " AND id_cocinero = :idCocinero";
        }
        if ($puntuacionMesa) {
            $consultaStr .= " AND puntuacion_mesa >= :puntuacionMesa";
        }
        if ($puntuacionRestaurante) {
            $consultaStr .= " AND puntuacion_restaurante >= :puntuacionRestaurante";
        }
        if ($puntuacionMozo) {
            $consultaStr .= " AND puntuacion_mozo >= :puntuacionMozo";
        }
    
        $consulta = $objAccesoDatos->prepararConsulta($consultaStr);
        
        if ($idMesa) {
            $consulta->bindValue(':idMesa', $idMesa, PDO::PARAM_INT);
        }
        if ($idMozo) {
            $consulta->bindValue(':idMozo', $idMozo, PDO::PARAM_INT);
        }
        if ($idCocinero) {
            $consulta->bindValue(':idCocinero', $idCocinero, PDO::PARAM_INT);
        }
        if ($puntuacionMesa) {
            $consulta->bindValue(':puntuacionMesa', $puntuacionMesa, PDO::PARAM_INT);
        }
        if ($puntuacionRestaurante) {
            $consulta->bindValue(':puntuacionRestaurante', $puntuacionRestaurante, PDO::PARAM_INT);
        }
        if ($puntuacionMozo) {
            $consulta->bindValue(':puntuacionMozo', $puntuacionMozo, PDO::PARAM_INT);
        }
    
        $consulta->execute();
    
        $arrayEncuestas = array();
        foreach ($consulta->fetchAll(PDO::FETCH_OBJ) as $encuesta) {
            array_push($arrayEncuestas, self::transformarEncuesta($encuesta));
        }
    
        return $arrayEncuestas;
    }

    public static function modificarEncuesta($encuesta)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE Encuestas SET id_mesa = ?, id_mozo = ?, id_cocinero = ?, puntuacion_mesa = ?, puntuacion_restaurante = ?, puntuacion_mozo = ?, puntuacion_cocinero = ?, experiencia_texto = ? WHERE id_encuesta = ? AND eliminado = " . ACTIVO);
        $consulta->bindParam(1, $encuesta->idMesa);
        $consulta->bindParam(2, $encuesta->idMozo);
        $consulta->bindParam(3, $encuesta->idCocinero);
        $consulta->bindParam(4, $encuesta->puntuacionMesa);
        $consulta->bindParam(5, $encuesta->puntuacionRestaurante);
        $consulta->bindParam(6, $encuesta->puntuacionMozo);
        $consulta->bindParam(7, $encuesta->puntuacionCocinero);
        $consulta->bindParam(8, $encuesta->experienciaTexto);
        $consulta->bindParam(9, $encuesta->idEncuesta);
        $consulta->execute();
    }

    public static function borrarEncuesta($encuesta)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE Encuestas WHERE id_encuesta = ? SET eliminado = " . ACTIVO);
        $consulta->bindParam(1, $encuesta->idEncuesta);
        $consulta->execute();
    }

    private static function transformarEncuesta($encuesta)
    {
        $obj = new Encuesta();
        $obj->idPedido = $encuesta->id_pedido;
        $obj->idEncuesta = $encuesta->id_encuesta;
        $obj->idMesa = $encuesta->id_mesa;
        $obj->idMozo = $encuesta->id_mozo;
        $obj->idCocinero = $encuesta->id_cocinero;
        $obj->puntuacionMesa = $encuesta->puntuacion_mesa;
        $obj->puntuacionRestaurante = $encuesta->puntuacion_restaurante;
        $obj->puntuacionMozo = $encuesta->puntuacion_mozo;
        $obj->puntuacionCocinero = $encuesta->puntuacion_cocinero;
        $obj->experienciaTexto = $encuesta->experiencia_texto;
        $obj->fechaCreacion = DateTime::createFromFormat('Y-m-d H:i:s', $encuesta->fecha_creacion, new DateTimeZone("America/Argentina/Buenos_Aires"));

        return $obj;
    }
}
