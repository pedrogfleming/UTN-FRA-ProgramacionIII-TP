create table if not exists Usuarios (
idUsuario INT auto_increment primary key,
nombre VARCHAR(255) not null,
fechaCreacion DATETIME not null,
fechaFinalizacion DATETIME,
user VARCHAR(255) not null,
password VARCHAR(255) not null,
sector VARCHAR(255) not null,
tipo VARCHAR(255) not null,
unique (nombre,user));

CREATE TABLE IF NOT EXISTS Productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    tiempo_preparacion INT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    estado ENUM('Activo', 'Inactivo') NOT NULL,
    sector VARCHAR(255) NOT NULL,
    fecha_creacion DATETIME NOT NULL,
    UNIQUE (titulo)
)
        
CREATE TABLE IF NOT EXISTS Mesas (
    idMesa INT AUTO_INCREMENT PRIMARY KEY,
    codigo_identificacion  VARCHAR(5) NOT NULL,
    estado ENUM('con_cliente_esperando_pedido', 'con_cliente_comiendo', 'con_cliente_pagando', 'cerrada') NOT NULL,
    fechaApertura DATETIME NOT NULL,
    fechaCierre DATETIME,
    UNIQUE (codigo_identificacion)
)

CREATE TABLE IF NOT EXISTS Pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    codigo_identificacion VARCHAR(5) NOT NULL,
    id_usuario INT NOT NULL,
    id_producto INT NOT NULL,
    id_mesa INT NOT NULL,
    cantidad INT NOT NULL,
    fecha_estimada_finalizacion DATETIME NOT NULL,
    fecha_finalizacion DATETIME,
    sector VARCHAR(255) NOT NULL,
    estado ENUM('pendiente', 'en preparación', 'listo para servir', 'cancelado') NOT NULL,
    FOREIGN KEY (id_mesa) REFERENCES Mesas (idMesa) ON DELETE CASCADE ON UPDATE cascade,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios (idUsuario) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES Productos (id_producto) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (codigo_identificacion)
)