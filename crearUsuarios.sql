SET @password = '123_admin';
SET @hashed_password = PASSWORD(@password);

select @hashed_password;

INSERT INTO db_utn_tp_comanda.usuarios
(nombre, fechaCreacion, fechaFinalizacion, `user`, password, sector, tipo)
VALUES('cristobal', '2023-11-14 11:54:37.000', NULL, 'cristobal_socio', @hashed_password, 'administracion', 'admin');

select * from db_utn_tp_comanda.usuarios;

SET @password = '123_mozo';
SET @hashed_password = PASSWORD(@password);

INSERT INTO db_utn_tp_comanda.usuarios
(nombre, fechaCreacion, fechaFinalizacion, `user`, password, sector, tipo)
VALUES('oscar', '2023-11-15 11:54:37.000', NULL, 'oscar_mozo', @hashed_password, 'mesas', 'mozo');

SET @password = '123_cocinero';
SET @hashed_password = PASSWORD(@password);

INSERT INTO db_utn_tp_comanda.usuarios
(nombre, fechaCreacion, fechaFinalizacion, `user`, password, sector, tipo)
VALUES('susana', '2023-02-15 11:54:37.000', NULL, 'susana_cocinera', @hashed_password, 'cocina', 'cocinero');

START TRANSACTION;


selec * from usuarios where username = 