<?php
require_once DB . "/AccesoDatos.php";

class Mesa
{
    public $idMesa;
    public $mozo;
    public $importeTotal;
    public $nombreCliente;
    public $estado;
    public $fechaApertura;
    public $fechaCierre;

    const ESTADO_PENDIENTE = "pendiente";
    const ESTADO_PREPARACION = "en preparación";
    const ESTADO_LISTO = "listo para servir";
    const ESTADO_CANCELADO = "cancelado";

    public function crearMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO Mesas (mozo,  importeTotal, nombreCliente, estado, fechaApertura, fechaCierre) VALUES (?, ?, ?, ?, ?, ?)");
        $fechaApertura = new DateTime("now", new DateTimeZone("America/Argentina/Buenos_Aires"));
        $fechaCierre = null;
        $fechaString = date_format($fechaApertura, 'Y-m-d H:i:s');
        $consulta->bindParam(1, $this->mozo);
        $consulta->bindParam(2, $this->importeTotal);
        $consulta->bindParam(3, $this->nombreCliente);
        $consulta->bindParam(4, $this->estado);
        $consulta->bindParam(5, $fechaString);
        $consulta->bindParam(6, $fechaCierre);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodasLasMesas()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM Mesas");
        $consulta->execute();
        $arrayMesas = array();
        foreach ($consulta->fetchAll(PDO::FETCH_OBJ) as $registro) {
            array_push($arrayMesas, Mesa::transformarRegistro($registro));
        }
        return $arrayMesas;
    }

    public static function obtenerMesa($id)
    {
        $rtn = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM Mesas WHERE idMesa = ?");
        $consulta->bindParam(1, $id);
        $consulta->execute();

        $registro = $consulta->fetch(PDO::FETCH_OBJ);
        if ($registro != false) {
            $rtn = Mesa::transformarRegistro($registro);
        }

        return $rtn;
    }

    private static function transformarRegistro($registro)
    {
        $mesa = new Mesa();
        $mesa->idMesa = $registro->idMesa;
        $mesa->mozo = $registro->mozo;
        $mesa->importeTotal = $registro->importeTotal;
        $mesa->nombreCliente = $registro->nombreCliente;
        $mesa->estado = $registro->estado;

        if ($registro->fechaApertura) {
            $mesa->fechaApertura = DateTime::createFromFormat('Y-m-d H:i:s', $registro->fechaApertura, new DateTimeZone("America/Argentina/Buenos_Aires"));
        } else {
            $mesa->fechaApertura = null;
        }

        if ($registro->fechaCierre) {
            $mesa->fechaCierre = DateTime::createFromFormat('Y-m-d H:i:s', $registro->fechaCierre, new DateTimeZone("America/Argentina/Buenos_Aires"));
        } else {
            $mesa->fechaCierre = null;
        }

        return $mesa;
    }

    public function actualizarMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE Mesas SET mozo = ?, importeTotal = ?, nombreCliente = ?, estado = ?, fechaCierre = ? WHERE idMesa = ?");
        $fechaCierre = new DateTime("now", new DateTimeZone("America/Argentina/Buenos_Aires"));
        $consulta->bindParam(1, $this->mozo);
        $consulta->bindParam(2, $this->importeTotal);
        $consulta->bindParam(3, $this->nombreCliente);
        $consulta->bindParam(4, $this->estado);
        $consulta->bindParam(5, $fechaCierre);
        $consulta->bindParam(6, $this->idMesa);
        $consulta->execute();
    }

    public function eliminarMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("DELETE FROM Mesas WHERE idMesa = ?");
        $consulta->bindParam(1, $this->idMesa);
        $consulta->execute();
    }
}
