<?php

namespace davidcmg\batea\Controllers;

use davidcmg\batea\Config;
use davidcmg\batea\Core\Page;
use davidcmg\batea\Core\View;
use davidcmg\batea\Models\Server;

/**
 * Controlador para la página principal del panel de control.
 * En esta página se mostrará la información más importante de la aplicación.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Admin extends Page
{
	/**
	 * GET: /admin
	 * Página principal del panel de control.
	 * Se recopila información de utilidad del servidor, como puede ser el uso
	 * del disco duro y la carga del sistema.
	 * También se muestran los servidores vinculados, así como sus respectivas
	 * tareas y las copias realizadas dentro de cada tarea.
	 *
	 * @return void
	 */
	public function show()
	{
		// Recopilación de la carga del sistema
		$load1 = sys_getloadavg()[0];
		$load5 = sys_getloadavg()[1];
		$load15 = sys_getloadavg()[2];

		// Recopilación del hostname y de la ip del servidor
		$hostname = gethostname();
		$ip = gethostbyname($hostname);

		// Uso del disco del servidor
		$disk = Config::APP_ROOT . Config::APP_BACKUP_DIR;
		$diskProgress = floor(100 * disk_free_space($disk) / disk_total_space($disk));
		$diskFree = disk_free_space($disk);
		$si_prefix = ['B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB'];
		$base = 1024;
		$class = min((int) log($diskFree, $base), count($si_prefix) - 1);
		$free = sprintf('%1.2f', $diskFree / pow($base, $class)) . ' ' . $si_prefix[$class];

		// Servidores > Tareas > backups
		$servers = new Server();
		$servers = $servers->allObjects();
		$html = '';
		foreach ($servers as $server) {
			$html .= $this->createServerTable($server);
		}

		// Muestra la plantilla
		$template = new View(
			'Admin.html',
			compact(['html', 'hostname', 'ip', 'diskProgress', 'free', 'load1', 'load5', 'load15'])
		);
		$template->render();
	}
}
