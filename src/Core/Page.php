<?php

namespace davidcmg\batea\Core;

use davidcmg\batea\Config;
use davidcmg\batea\Models\Task;
use IntlDateFormatter;

/**
 * Clase abstracta para las páginas del dashboard.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
abstract class Page
{
	/**
	 * Se necesita la URI de la petición para generar los botones.
	 *
	 * @var string
	 */
	protected $uri;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		Auth::isLogged();
		$request = $GLOBALS['request'];
		$this->uri = $request->getUri();
	}

	/**
	 * Genera una tabla con un listado.
	 *
	 * @param string $table   Nombre de la tabla
	 * @param array  $columns Columnas que se quieren mostrar. Deben coincidir con la base de datos.
	 * @param array  $data    Array con los datos
	 * @param string $uri     Uri para generar los enlaces de los botones
	 * @param string $tabla   Se utiliza para añadir el data-target del botón para la ventana modal
	 *
	 * @return string
	 */
	public function createTable($columns, $data, $uri, $nombre)
	{
		$columns = array_flip($columns);

		$tabla = "	<tr>\n";
		foreach ($data as $line) {
			$id = $line['id'];
			foreach (array_intersect_key($line, $columns) as $dat) {
				$tabla .= "             <td>$dat</td>\n";
			}
			$tabla .= <<<EOD
					<td class="action">
						<a class="btn btn-success" href="$uri/$id" id="btn-view-$id" title="Mostrar">
							<i class='bx bxs-search' ></i>
						</a>
						<a class="btn btn-info" href="$uri/$id/edit" id="btn-edit-$id" title="Editar">
							<i class='bx bxs-edit' ></i>
						</a>
						<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalBorrar$nombre" id="btn-delete-$id" data-id="$id"><i class='bx bxs-trash' ></i></button>
					</td>
				</tr>\n
			EOD;
		}

		return $tabla;
	}

	/**
	 * Para la página principal del dashboard, crea una tabla para cada server.
	 *
	 * @param \davidcmg\src\Core\Models\Server $server
	 *
	 * @return string
	 */
	public function createServerTable($server)
	{
		$fmt = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::LONG, Config::APP_TIMEZONE, IntlDateFormatter::GREGORIAN);

		$lastStatus = $server->hasLast('server_status', 'server_id');
		$dateTime = date_create($lastStatus['date']);
		$dateTime = $fmt->format($dateTime);
		//$dateTime = date_format($dateTime, 'g:ia \o\n l jS F Y');
		$html = <<<EOD
		<div class="accordion server-accordion" id="accordionServers">
			<div class="card mb-3">
				<div class="card-header" id="$server->id">
					<h2 class="mb-0">
						<button class="btn btn-link btn-block collapsed" type="button" data-toggle="collapse" data-target="#collapseServer$server->id" aria-expanded="false" aria-controls="collapseServer$server->id">
						<span class="float-left"><span class="badge badge-success"> $server->status</span> $server->host </span><span class="float-right"><i class="bx"></i> </span>
						</button>
					</h2>
				</div>
				<div id="collapseServer$server->id" class="collapse" aria-labelledby="$server->id" data-parent="#accordionServers">
				<div class="card-body">
					<div class="alert alert-secondary">
						Último cambio de estado: $dateTime
					</div>
					<div class="row">
		EOD;

		$tasks = $server->hasMany('tasks', 'server_id');

		foreach ($tasks as $task) {
			$html .= $this->createTaskTable($task);
		}
		$html .= <<<EOD
					</div>
				</div>
			  </div>
			</div>
		</div>
		EOD;

		//$htmlTasks = $this->createTaskTable($tasks);
		return $html;
	}

	/**
	 * Para la página principal del dashboard, crea una tabla de una tarea.
	 *
	 * @param array $task
	 *
	 * @return string
	 */
	public function createTaskTable($task)
	{
		$rows = $this->createTaskRow($task);
		$html = <<<EOD
		<div class="col-md-6">
			<div class="card task-card mb-3">
			<div class="card-header">
				<span class="mb-0">
				<strong>{$task['name']}</strong> <small class="text-muted">	Tarea #{$task['id']}</small>
				</span>
			</div>
			<table class="table table-sm table-hover backups">
				<thead>
				<tr>
					<th scope="col" class="text-center"><i class='bx bx-history' ></i></th>
					<th scope="col"><i class='bx bx-calendar-star' ></i> {$task['cron']}</th>
					<th scope="col"><i class='bx bx-cloud' ></i> {$task['source']}</th>
					<th scope="col"></th>
				</tr>
				</thead>
					<tbody>
		EOD;
		$html .= $rows;
		$html .= <<<EOD
					</tbody>
			</table>
			</div>
		</div>
		EOD;

		return $html;
	}

	/**
	 * Para la página principal del dashboard, crea una línea de una tarea.
	 *
	 * @param array $task
	 *
	 * @return string
	 */
	public function createTaskRow($task)
	{
		$const = Task::getConstants();
		$limit = (int) $task['depth'];
		$limit = $limit + 1;
		$sql = 'SELECT * FROM task_status WHERE task_id=' . $task['id'] . ' ORDER BY id DESC LIMIT ' . $limit;
		$conn = new SQLite();
		$res = $conn->query($sql);
		if (empty($res)) {
			return;
		}

		$taskFolder = $const['folder_prefix'] . $task['id'] . '/';
		$backupFolder = '';
		$html = '';
		foreach ($res as $key => $row) {
			$hist = $key - 2 * $key;
			$trClass = '';
			if ($key == 0) {
				$backupFolder = $taskFolder . $const['last'];
				$backupLog = $taskFolder . $const['folder_prefix'] . $task['id'] . '.log';
				$trClass = 'class=""';
			} else {
				$backupFolder = $taskFolder . $const['prev_prefix'] . $key . '/';
				$backupLog = $backupFolder . $const['folder_prefix'] . $task['id'] . '.log';
			}
			$backupFolder = rtrim($backupFolder, '/');
			$start = date('d/m/Y H:i', strtotime($row['start']));
			$end = date('H:i', strtotime($row['end']));
			if ($row['status'] == 'ok') {
				$badge = '<span class="badge badge-pill badge-success">Success</span>';
			} else {
				$badge = '<span class="badge badge-pill badge-danger">Error</span>';
			}
			$html .= <<<EOD
			<tr $trClass>
				<th scope="row" class="text-center">$hist</th>
				<td>$start - $end</td>
				<td>
					<div class="btn-group btn-group-sm" role="group" aria-label="Opciones">
						<a href="/files/log/$backupLog" type="button" class="btn btn-outline-dark"><i class='bx bxs-show' ></i></a>
						<a href="/files/backup/$backupFolder" type="button" class="btn btn-outline-dark"><i class='bx bx-download' ></i></a>
						<button type="button" class="btn btn-outline-dark" data-toggle="modal" data-target="#modalRestaurar" id="btn-restore-{$task['id']}" data-id="{$task['id']}" data-depth="$hist"><i class='bx bx-reset' ></i></button>
					</div>
				</td>
				<td>$badge</td>
			</tr>
			EOD;
		}

		return $html;
	}
}
