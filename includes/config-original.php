<?php

session_start();
header( 'Content-Type: text/html; charset=UTF-8' );

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );
date_default_timezone_set( 'America/Caracas' );

define('ABSPATH', dirname( dirname( __FILE__ ) ) . '/');
define('TEMPLATE_PATH', dirname( dirname( __FILE__ ) ) . '/templates/');
define('NONCE_SALT', '$%/?#$!)=');

require_once(ABSPATH . 'includes/functions.php');
require_once(ABSPATH . 'includes/mysql_connect.php');

//Datos de la conexion por MYSQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'aguaparko');
define('DB_USER', 'root');
define('DB_PASS', 'tecnomysql');

$db = new mysql_connection( DB_HOST, DB_NAME, DB_USER, DB_PASS );

$options = get_options();

define('CURRENT_TEMPLATE', $options["default_theme"]);
define('SITE_URL', getUrl() . $options["root_path"]);
define('TEMPLATE_URL', SITE_URL . 'templates/' . CURRENT_TEMPLATE);
define('SITE_NAME', $options["site_name"]);


require_once(ABSPATH . 'includes/securimage/securimage.php');
require_once(ABSPATH . 'includes/twig_loader.php');

define('PERM_MSG', 'Disculpe, no tiene suficientes permisos para acceder a esta sección');