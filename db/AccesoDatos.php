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
    private function CrearDb()
    {
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

        $crear_tabla_mesas = <<<SQL
        CREATE TABLE IF NOT EXISTS Mesas (
            idMesa INT AUTO_INCREMENT PRIMARY KEY,
            mozo VARCHAR(255) NOT NULL,
            comanda INT NOT NULL,
            importeTotal DECIMAL(10, 2) NOT NULL,
            nombreCliente VARCHAR(255) NOT NULL,
            estado ENUM('pendiente', 'en preparación', 'listo para servir', 'cancelado') NOT NULL,
            fechaApertura DATETIME NOT NULL,
            fechaCierre DATETIME
        )
        SQL;

        $this->objetoPDO->exec($crear_tabla_mesas);


        $crear_tabla_comandas = <<<SQL
        CREATE TABLE IF NOT EXISTS comandas (
            id_comanda INT AUTO_INCREMENT PRIMARY KEY,
            id_mesa INT NOT NULL,
            id_mozo INT NOT NULL,
            fecha_comanda DATETIME NOT NULL,
            FOREIGN KEY (id_mesa) REFERENCES mesas (id_mesa),
            FOREIGN KEY (id_mozo) REFERENCES usuarios (ID_USUARIO)
        )
        SQL;
        $this->objetoPDO->exec($crear_tabla_comandas);

        $crear_tabla_pedidos = <<<SQL
        CREATE TABLE IF NOT EXISTS pedidos (
            id_pedido INT AUTO_INCREMENT PRIMARY KEY,
            id_comanda INT NOT NULL,
            id_usuario INT NOT NULL,
            id_producto INT NOT NULL,
            cantidad INT NOT NULL,
            fecha_estimada_finalizacion DATETIME NOT NULL,
            fecha_finalizacion DATETIME,
            sector VARCHAR(255) NOT NULL,
            estado ENUM('pendiente', 'en preparación', 'listo para servir', 'cancelado') NOT NULL,
            FOREIGN KEY (id_comanda) REFERENCES comandas (id_comanda),
            FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario),
            FOREIGN KEY (id_producto) REFERENCES productos (id_producto)
        )
        SQL;

        $this->objetoPDO->exec($crear_tabla_pedidos);
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
