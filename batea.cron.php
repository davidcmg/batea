<?php

use davidcmg\batea\Config;
use davidcmg\batea\Models\Server;
use davidcmg\batea\Models\Task;

require_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set(Config::APP_TIMEZONE);

/*
 * Comprobación del estado de los servidores cada 5 minutos
 */
if (date('i', time()) % 5 == 0) {
	$servers = new Server();
	$servers = $servers->allObjects();
	foreach ($servers as $server) {
		$server->addStatus($server->isOnline());
	}
}

/*
 * Comprueba si es necesario ejecutar las tareas.
 */
$tasks = new Task();
$tasks = $tasks->allObjects();
foreach ($tasks as $task) {
	$task->check();
}
