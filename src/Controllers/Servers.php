<?php

namespace davidcmg\batea\Controllers;

use davidcmg\batea\Core\Auth;
use davidcmg\batea\Core\Page;
use davidcmg\batea\Core\View;
use davidcmg\batea\Models\Server;

/**
 * Controlador para los servidores remotos clientes, de los que se podrán
 * realizar copias de seguridad.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Servers extends Page
{
	/**
	 * GET: /admin/servers
	 * Genera la tabla de servidores y muestra la página.
	 *
	 * @return void
	 */
	public function all()
	{
		$servers = new Server();
		$servers = $servers->all();
		$status = '';
		foreach ($servers as $key => $server) {
			if ($server['status'] == 'online') {
				$status = '<span class="badge badge-pill badge-success">online</span>';
			} else {
				$status = '<span class="badge badge-pill badge-danger">offline</span>';
			}

			$servers[$key]['status'] = $status;
		}
		$listaServers = $this->createTable(['id', 'user', 'host', 'port', 'status'], $servers, $this->uri, 'Servidor');

		$template = new View(
			'servers-list.html',
			compact(['listaServers'])
		);
		$template->render();
	}

	/**
	 * GET: /admin/servers/create
	 * Muestra el formulario para el nuevo servidor.
	 *
	 * @return void
	 */
	public function new()
	{
		$template = new View(
			'servers-create.html',
			compact([])
		);
		$template->render();
	}

	/**
	 * POST: /admin/servers/create
	 * Crea un servidor en la base de datos.
	 * Se pide la contraseña para poder hacer la primera conexión pero no se
	 * guarda en la BD.
	 * Una vez conectado con contraseña se copia la clave pública y se guarda un
	 * estado del mismo para llevar un control (online/offline).
	 *
	 * @todo controlar que los datos del formulario son correctos
	 *
	 * @return void
	 */
	public function create()
	{
		$server = new Server();
		$server->user = $_POST['user'];
		$server->host = $_POST['host'];
		$server->port = $_POST['port'];
		$pass = $_POST['password'];

		if (!$server->isOnline()) {
			Auth::setMessage('El servidor no es accesible. ' . $server->connError, 'danger');
			redirect('/admin/servers/create');
			exit();
		}
		$server->is_online = true;
		$ssh = $server->loginWithPass($pass);

		if ($ssh == false) {
			Auth::setMessage($server->connError, 'danger');
			redirect('/admin/servers/create');
			exit();
		}
		$copy = $server->copyPublicKey($ssh);
		if (intval($copy) != 0) {
			Auth::setMessage('Error al copiar la clave en ~/.ssh/authorized_keys', 'danger');
			redirect('/admin/servers/create');
			exit();
		}
		$id = $server->create();

		$server->id = $id;
		$server->addStatus(true);

		Auth::setMessage('Servidor ' . $server->host . ' creado correctamente', 'success');
		redirect('/admin/servers');
	}

	/**
	 * GET: /admin/servers/:id
	 * Muestra al servidor que se pasa como parámetro en la URI.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function show($params)
	{
		extract($params);
		$server = $this->getServer($id);
		$host = $server->host;
		$user = $server->user;
		$port = $server->port;

		$template = new View('servers-show.html', compact(['id', 'host', 'user', 'port']));
		$template->render();
	}

	/**
	 * Funcion interna para que retorne una instancia de server dada un id.
	 *
	 * @param int $id
	 *
	 * @return object
	 */
	private function getServer($id)
	{
		$id = intval($id);
		$server = new Server();
		$server = $server->getById($id);

		return $server;
	}

	/**
	 * GET: /admin/servers/:id/edit
	 * Muestra el formulario de edición.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function getEdit($params)
	{
		extract($params);
		$server = $this->getServer($id);
		$host = $server->host;
		$user = $server->user;
		$port = $server->port;

		$template = new View('servers-edit.html', compact(['id', 'host', 'port', 'user']));
		$template->render();
	}

	/**
	 * POST: /admin/servers/:id/edit
	 * Tras modificar los datos en el formulario de edición se guardan en la
	 * base de datos.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function postEdit($params)
	{
		extract($params);
		$server = $this->getServer($id);
		$server->host = $_POST['host'];
		$server->port = $_POST['port'];
		$server->save();
		Auth::setMessage('Datos modificados correctamente', 'success');
		redirect('/admin/servers');
	}

	/**
	 * POST: /admin/servers/:id/delete
	 * Borra un servidor de la base de datos.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function delete($params)
	{
		extract($params);
		$server = $this->getServ($id);
		$host = $server->host;
		$server->delete();
		Auth::setMessage('Servidor ' . $host . ' eliminado correctamente', 'success');
		redirect('/admin/servers');
	}

	/**
	 * Funcion interna para que retorne un objeto server dada un id.
	 *
	 * @param int|string $id
	 *
	 * @return object
	 */
	private function getServ($id)
	{
		$id = intval($id);
		$server = new Server();
		$server = $server->getById($id);

		return $server;
	}
}
