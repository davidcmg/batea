<?php

namespace davidcmg\batea\Controllers;

use davidcmg\batea\Config;
use davidcmg\batea\Core\Auth;
use davidcmg\batea\Core\View;

/**
 * Controlador del formulario de login.
 * Se implementan métodos para mostrar el formulario, validar las credenciales y
 * cerrar la sesión.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Login
{
	/**
	 * GET: /login
	 * Muestra el formulario de login.
	 *
	 * @return void
	 */
	public function show()
	{
		$title = Config::APP_NAME;

		$template = new View(
			'Login.html',
			compact(['title'])
		);
		$template->render();
	}

	/**
	 * POST: /login
	 * Comprueba que el usuario es válido.
	 * Se recoge el usuario y la contraseña para comprobar que son válidos con
	 * el método checkPass.
	 * En el caso de que las credenciales sean inválidas, redirige al usuario al
	 * formulario de login.
	 *
	 * @return void
	 */
	public function check()
	{
		$username = $_POST['username'];
		$password = $_POST['password'];

		if (!Auth::checkPass($username, $password)) {
			redirect('/login');
			exit();
		}
	}

	/**
	 * GET: /logout
	 * Cierra la sesión y redirige al usuario al formulario de login.
	 *
	 * @return void
	 */
	public function logout()
	{
		Auth::removeSession();
		redirect('/login');
	}
}
