<?php

namespace davidcmg\batea\Controllers;

use davidcmg\batea\Core\Auth;
use davidcmg\batea\Core\Page;
use davidcmg\batea\Models\User;
use davidcmg\batea\Core\View;

/**
 * Controlador para los usuarios de la aplicación.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Users extends Page
{
	/**
	 * GET: /admin/users
	 * Genera la tabla de usuarios y muestra la página.
	 *
	 * @return void
	 */
	public function all()
	{
		$users = new User();
		$users = $users->all();
		$listaUsuarios = $this->createTable(['id', 'username'], $users, $this->uri, 'Usuario');
		$template = new View(
			'users-list.html',
			compact(['listaUsuarios'])
		);
		$template->render();
	}

	/**
	 * GET: /admin/users/create
	 * Muestra el formulario para en nuevo usuario.
	 *
	 * @return void
	 */
	public function new()
	{
		$template = new View(
			'users-create.html',
			compact([])
		);
		$template->render();
	}

	/**
	 * POST: /admin/users/create
	 * Crea un usuario en la base de datos.
	 *
	 * @todo Verificar los datos que se envían como parámetro
	 *
	 * @return void
	 */
	public function create()
	{
		$user = new User();
		$user->username = $_POST['username'];
		$user->password = $_POST['password'];
		$user->create();
		Auth::setMessage('Usuario ' . $user->username . ' creado correctamente', 'success');
		redirect('/admin/users');
	}

	/**
	 * GET: /admin/users/:id
	 * Muestra al usuario que se pasa como parámetro en la URI.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function show($params)
	{
		extract($params);
		$user = $this->getUser($id);
		$username = $user->username;

		$template = new View('users-show.html', compact(['id', 'username']));
		$template->render();
	}

	/**
	 * GET: /admin/users/:id/edit
	 * Muestra el formulario de edición.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function getEdit($params)
	{
		extract($params);
		$user = $this->getUser($id);
		$username = $user->username;
		$template = new View('users-edit.html', compact(['id', 'username']));
		$template->render();
	}

	/**
	 * POST: /admin/users/:id/edit
	 * Modifica el usuario.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function postEdit($params)
	{
		extract($params);
		$user = $this->getUser($id);
		$password = $_POST['password'];
		if ($password != '') {
			$user->password = $password;
			$user->save();
			Auth::setMessage('Contraseña modificada correctamente', 'success');
		} else {
			Auth::setMessage('No se ha modificado la contraseña', 'danger');
		}
		redirect('/admin/users');
	}

	/**
	 * POST: /admin/users/:id/delete
	 * Borra un usuario.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function delete($params)
	{
		extract($params);
		$user = $this->getUser($id);
		if (Auth::getUser()->id == $user->id) {
			Auth::setMessage('El usuario a borrar es el activo', 'danger');
			redirect('/admin/users');
			exit();
		}
		$username = $user->username;
		$user->delete();
		Auth::setMessage('Usuario ' . $username . ' eliminado correctamente', 'success');
		redirect('/admin/users');
	}

	/**
	 * Funcion interna para que retorne un objeto usuario dada un id.
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	private function getUser($id)
	{
		$id = intval($id);
		$user = new User();
		$user = $user->getById($id);

		return $user;
	}
}
