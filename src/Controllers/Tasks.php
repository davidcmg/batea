<?php

namespace davidcmg\batea\Controllers;

use davidcmg\batea\Core\Auth;
use davidcmg\batea\Core\Page;
use davidcmg\batea\Core\View;
use davidcmg\batea\Models\Server;
use davidcmg\batea\Models\Task;

/**
 * Controlador para las tareas de las copias de seguridad.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Tasks extends Page
{
	/**
	 * GET: /admin/tasks
	 * Genera la tabla de tareas y muestra la página.
	 *
	 * @return void
	 */
	public function all()
	{
		$tasks = new Task();
		$tasks = $tasks->all();
		$listaTasks = $this->createTable(['id', 'name'], $tasks, $this->uri, 'Tarea');
		$template = new View(
			'tasks-list.html',
			compact(['listaTasks'])
		);
		$template->render();
	}

	/**
	 * GET: /admin/tasks/create
	 * Muestra el formulario para la nueva tarea.
	 *
	 * @return void
	 */
	public function new()
	{
		$servers = new Server();
		$servers = $servers->all();
		$options = '';
		foreach ($servers as $server) {
			$options .= '<option value="' . $server['id'] . '">' . $server['host'] . '</option>' . PHP_EOL;
		}
		$template = new View(
			'tasks-create.html',
			compact(['options'])
		);
		$template->render();
	}

	/**
	 * POST: /admin/tasks/create
	 * Crea una tarea en la base de datos.
	 *
	 * @todo controlar los datos que recibe como parámetros
	 *
	 * @return void
	 */
	public function create()
	{
		$task = new Task();
		$task->name = $_POST['name'];
		$task->server_id = $_POST['server'];
		$task->cron = $_POST['cron'];
		$task->depth = $_POST['depth'];
		$task->source = $_POST['source'];
		$task->exclude = implode(',', preg_split('/\r\n|\r|\n/', $_POST['exclude']));
		$task->email = $_POST['email'];

		$task->create();

		Auth::setMessage('Tarea ' . $task->name . ' creada correctamente', 'success');
		redirect('/admin/tasks');
	}

	/**
	 * GET: /admin/tasks/:id
	 * Muestra la tarea que se pasa como parámetro en la URI.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function show($params)
	{
		extract($params);
		$task = $this->getTask($id);
		$server = new Server();
		$server = $server->getById(intval($task->server_id));
		$name = $task->name;
		$host = $server->host;
		$cron = $task->cron;
		$depth = $task->depth;
		$source = $task->source;
		$exclude = str_replace(',', PHP_EOL, $task->exclude);
		$email = $task->email;

		$template = new View('tasks-show.html', compact(['name', 'host', 'cron', 'depth', 'source', 'exclude', 'email']));
		$template->render();
	}

	/**
	 * GET: /admin/tasks/:id/edit
	 * Muestra el formulario de edición.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function getEdit($params)
	{
		extract($params);
		$task = $this->getTask($id);
		$server = new Server();
		$server = $server->getById(intval($task->server_id));
		$name = $task->name;
		$host = $server->host;
		$cron = $task->cron;
		$depth = $task->depth;
		$source = $task->source;
		$exclude = str_replace(',', PHP_EOL, $task->exclude);
		$email = $task->email;

		$template = new View('tasks-edit.html', compact(['id', 'name', 'host', 'cron', 'depth', 'source', 'exclude', 'email']));
		$template->render();
	}

	/**
	 * POST: /admin/tasks/:id/edit
	 * Modifica el servidor según los cambios que se realizasen
	 * en el formulario de edición.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function postEdit($params)
	{
		extract($params);
		$task = $this->getTask($id);
		$task->name = $_POST['name'];
		$task->cron = $_POST['cron'];
		$task->depth = $_POST['depth'];
		$task->source = $_POST['source'];
		$task->exclude = implode(',', preg_split('/\r\n|\r|\n/', $_POST['exclude']));
		$task->email = $_POST['email'];
		$task->save();
		Auth::setMessage('Datos modificados correctamente', 'success');
		redirect('/admin/tasks');
	}

	/**
	 * POST: /admin/tasks/:id/delete
	 * Borra una tarea.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function delete($params)
	{
		extract($params);
		$task = $this->getTask($id);
		$name = $task->name;
		$task->delete();
		Auth::setMessage('Tarea ' . $name . ' eliminada correctamente', 'success');
		redirect('/admin/tasks');
	}

	/**
	 * Funcion interna para que retorne una instancia de una tarea dada un id.
	 *
	 * @param int $id
	 *
	 * @return object
	 */
	private function getTask($id)
	{
		$id = intval($id);
		$task = new Task();
		$task = $task->getById($id);

		return $task;
	}

	/**
	 * POST: /admin/tasks/:id/:depth/restore
	 * Restaura una copia de seguridad al servidor remoto.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function restore($params)
	{
		$id = $params['id'];
		$depth = $params['depth'];
		$task = new Task();
		$task = $task->getById(intval($id));
		if ($depth[0] == '-') {
			$depth = ltrim($depth, '-');
		}
		$task->restore($depth);
	}
}
