<?php

use Slim\Psr7\Environment;

class AccesoDatos
{
    private static $objAccesoDatos;
    private $objetoPDO;

    private function __construct()
    {
        try {
            $this->CrearDb();
        } catch (PDOException $e) {
            print "Error: " . $e->getMessage();
            die();
        }
    }
    private function CrearDb(){
        $dbnombre = "db_utn_tp_comanda";
        $dbusername = "root";
        $dbpassword = "";
        $this->objetoPDO = new PDO("mysql:host=localhost", $dbusername, $dbpassword);
        $this->objetoPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbnombre = "`" . str_replace("`", "``", $dbnombre) . "`";
        $this->objetoPDO->query("CREATE DATABASE IF NOT EXISTS $dbnombre");
        $this->objetoPDO->query("use $dbnombre");

        $crear_tabla_usuarios = <<<SQL
        CREATE TABLE IF NOT EXISTS Usuarios (
            idUsuario INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255),
            fechaCreacion DATETIME,
            fechaFinalizacion DATETIME,
            user VARCHAR(255),
            password VARCHAR(255),
            sector VARCHAR(255),
            tipo VARCHAR(255)
        )
        SQL;

        $this->objetoPDO->exec($crear_tabla_usuarios);

        $crear_tabla_productos = <<<SQL
        CREATE TABLE IF NOT EXISTS Productos (
            id_producto INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(255) NOT NULL,
            tiempo_preparacion INT NOT NULL,
            precio DECIMAL(10, 2) NOT NULL,
            estado ENUM('Activo', 'Inactivo') NOT NULL,
            sector VARCHAR(255) NOT NULL,
            fecha_creacion DATETIME NOT NULL
        )
        SQL;

        $this->objetoPDO->exec($crear_tabla_productos);
    }

    public static function obtenerInstancia()
    {
        if (!isset(self::$objAccesoDatos)) {
            self::$objAccesoDatos = new AccesoDatos();
        }
        return self::$objAccesoDatos;
    }

    public function prepararConsulta($sql)
    {
        return $this->objetoPDO->prepare($sql);
    }

    public function obtenerUltimoId()
    {
        return $this->objetoPDO->lastInsertId();
    }

    public function __clone()
    {
        trigger_error('ERROR: La clonación de este objeto no está permitida', E_USER_ERROR);
    }
}
