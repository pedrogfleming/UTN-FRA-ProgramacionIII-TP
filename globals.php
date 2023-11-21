<?php
// Desde adentro de ./app

define("CONTROLLERS","../Controllers");
define("DB","../db");
define("INTERFACES","../Interfaces");
define("MODELS","../Models");
define("MIDDLEWARES", "../Middlewares");
define("UTILS", "../Utils");
define("SETTINGS", "../settings.json");

// Roles
define("ROL_ADMIN", "admin");
define("ROL_SOCIO", "socio");
define("ROL_BARTENDER", "bartender");
define("ROL_CERVECERO", "cervecero");
define("ROL_MOZO", "mozo");
define("ROL_COCINERO", "cocinero");


// Sectores
define("SECTOR_COCINA", "cocina");
define("SECTOR_CERVECERIA", "cerveza");
define("SECTOR_MESAS", "mesas");
define("SECTOR_ADMINISTRACION", "administracion");
define("SECTOR_BAR", "bar");