<?php

use davidcmg\batea\Config;
use davidcmg\batea\Core\Http\Request;
use davidcmg\batea\Core\Http\Response;
use davidcmg\batea\Core\Router;

require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Helpers/helper.php';
date_default_timezone_set(Config::APP_TIMEZONE);
session_name('BATEA');
session_start();
$baseDir = dirname(__FILE__);

/*
 * Modo debug
 */
if (Config::APP_DEBUG) {
	error_reporting(E_ALL);
	require __DIR__ . '/Helpers/debug.php';
} else {
	ini_set('display_errors', 0);
	customError();
}
/*
 * HTTP.
 */
$request = new Request();
$response = new Response();

/**
 * Rutas.
 */
$routes = include __DIR__ . '/Routes.php';
$router = new Router($routes, $request, $response);
$router->run();
