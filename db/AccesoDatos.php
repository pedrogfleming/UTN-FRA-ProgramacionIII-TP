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
            nombre VARCHAR(255) NOT NULL,
            fechaCreacion DATETIME NOT NULL,
            fechaFinalizacion DATETIME,
            user VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            sector VARCHAR(255) NOT NULL,
            tipo VARCHAR(255) NOT NULL,
            eliminado BOOL NULL DEFAULT FALSE,
            UNIQUE (nombre, user)
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
            fecha_creacion DATETIME NOT NULL,
            eliminado BOOL NULL DEFAULT FALSE,
            UNIQUE (titulo)
        )
        SQL;

        $this->objetoPDO->exec($crear_tabla_productos);

        $crear_tabla_mesas = <<<SQL
        CREATE TABLE IF NOT EXISTS Mesas (
            idMesa INT AUTO_INCREMENT PRIMARY KEY,
            estado ENUM('con_cliente_esperando_pedido', 'con_cliente_comiendo', 'con_cliente_pagando', 'cerrada') NOT NULL,
            fechaApertura DATETIME NOT NULL,
            fechaCierre DATETIME,
            eliminado BOOL DEFAULT FALSE
            ) AUTO_INCREMENT=1000;
        SQL;

        $this->objetoPDO->exec($crear_tabla_mesas);

        $crear_tabla_pedidos = <<<SQL
        CREATE TABLE IF NOT EXISTS Pedidos (
            id_pedido INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NOT NULL,
            id_mesa INT NOT NULL,            
            fecha_creacion DATETIME NOT NULL,
            fecha_estimada_finalizacion DATETIME NOT NULL,            
            fecha_finalizacion DATETIME,
            importe_total DECIMAL NOT NULL,
            nombre_cliente VARCHAR(255) NOT NULL,
            eliminado BOOL DEFAULT FALSE,
            estado ENUM('pendiente', 'en preparación', 'listo para servir', 'cancelado') NOT NULL,
            FOREIGN KEY (id_mesa) REFERENCES Mesas (idMesa) ON DELETE CASCADE,
            FOREIGN KEY (id_usuario) REFERENCES Usuarios (idUsuario) ON DELETE CASCADE
        ) AUTO_INCREMENT=1000;
        SQL;
        $this->objetoPDO->exec($crear_tabla_pedidos);

        $crear_tabla_items_pedidos = <<<SQL
        CREATE TABLE IF NOT EXISTS ItemPedidos(
            id_pedido INT NOT NULL,
            id_producto INT NOT NULL,
            cantidad INT NOT NULL,
            fecha_creacion DATETIME NOT NULL,
            fecha_estimada_finalizacion DATETIME NOT NULL,
            fecha_finalizacion DATETIME,
            estado ENUM('pendiente', 'en preparación', 'listo para servir', 'cancelado') NOT NULL,
            eliminado BOOL DEFAULT FALSE,
            FOREIGN KEY (id_pedido) REFERENCES Pedidos (id_pedido) ON DELETE CASCADE,
            FOREIGN KEY (id_producto) REFERENCES Productos (id_producto) ON DELETE CASCADE
        )
        SQL;
        $this->objetoPDO->exec($crear_tabla_items_pedidos);

        $crear_tabla_encuestas = <<<SQL
            CREATE TABLE IF NOT EXISTS Encuestas (
            id_pedido INT NOT NULL,
            id_encuesta INT AUTO_INCREMENT PRIMARY KEY,
            id_mesa INT NOT NULL,
            id_mozo INT NOT NULL,
            id_cocinero INT NOT NULL,
            puntuacion_mesa INT NOT NULL,
            puntuacion_restaurante INT NOT NULL,
            puntuacion_mozo INT NOT NULL,
            puntuacion_cocinero INT NOT NULL,
            experiencia_texto VARCHAR(66) NOT NULL,
            fecha_creacion DATETIME NOT NULL,
            eliminado BOOL NULL DEFAULT FALSE,
            FOREIGN KEY (id_pedido) REFERENCES Pedidos (id_pedido) ON DELETE CASCADE,
            FOREIGN KEY (id_mesa) REFERENCES Mesas (idMesa) ON DELETE CASCADE,
            FOREIGN KEY (id_mozo) REFERENCES Usuarios (idUsuario) ON DELETE CASCADE,
            FOREIGN KEY (id_cocinero) REFERENCES Usuarios (idUsuario) ON DELETE CASCADE
        ) AUTO_INCREMENT=1000;
        SQL;
        $this->objetoPDO->exec($crear_tabla_encuestas);
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
