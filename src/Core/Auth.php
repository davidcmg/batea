<?php

namespace davidcmg\batea\Core;

use davidcmg\batea\Config;
use davidcmg\batea\Models\User;
use PDO;

/**
 * Clase para la autenticación del usuario.
 * Se gestiona la autenticación del usuario así como las sesiones.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Auth
{
	/**
	 * Comprueba si el password es correcto.
	 * Se busca al usuario en la base de datos y se comprueba que la contraseña
	 * coincide.
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return bool
	 */
	public static function checkPass($username, $password)
	{
		$db = new SQLite();
		$query = 'SELECT * FROM users WHERE username = :username LIMIT 1';
		$params = ['username' => $username];
		$class = 'davidcmg\batea\Models\User';
		$user = $db->query($query, $params, PDO::FETCH_CLASS, $class);

		if ($user) {
			if (password_verify($password, $user->password)) {
				self::createSession($user->id);
				redirect('/admin');
			}
		}
		self::setMessage('Datos incorrectos', 'danger');

		return false;
	}

	/**
	 * Crea la sesión.
	 *
	 * @param int $id ID de usuario
	 *
	 * @return bool
	 */
	public static function createSession($id)
	{
		$_SESSION['SESSION_LIFETIME'] = time();
		$_SESSION['login'] = $id;

		//setcookie('login', $id, time() + Config::SESSION_LIFETIME);

		return true;
	}

	/**
	 * Borra la sesión.
	 *
	 * @return bool
	 */
	public static function removeSession()
	{
		session_destroy();
		$_SESSION = null;
		unset($_SESSION);
		//setcookie('login', 0, time() - Config::SESSION_LIFETIME);

		return true;
	}

	/**
	 * Comprueba que el usuario está logueado, en caso contrario lo redirige al
	 * formulario de login.
	 *
	 * @return boolean
	 */
	public static function isLogged()
	{
		if (!isset($_SESSION['login']) || !$_SESSION['login']) {
			redirect('/login', 403);
		} elseif (time() - $_SESSION['SESSION_LIFETIME'] > Config::SESSION_LIFETIME) {
			self::removeSession();
			session_start();
			self::setMessage('Sesión expirada');
			redirect('/login');
		} else {
			$_SESSION['SESSION_LIFETIME'] = time();

			return true;
		}
	}

	/**
	 * Devuelve el usuario que está logueado.
	 *
	 * @return \davidcmg\batea\Models\User
	 */
	public static function getUser()
	{
		if (self::isLogged()) {
			$id = intval($_SESSION['login']);
			if (self::isLogged()) {
				$user = new User();
				$user = $user->getById($id);

				return $user;
			}
		}

		return null;
	}

	/**
	 * Establece un mensaje entre páginas.
	 *
	 * @param string $message texto
	 * @param string $type
	 *                        primary
	 *                        secondary
	 *                        success
	 *                        danger
	 *                        warning
	 *                        info
	 *                        light
	 *                        dark
	 *
	 * @return void
	 */
	public static function setMessage($message, $type = 'info')
	{
		$_SESSION['msg-type'] = $type;
		$_SESSION['msg'] = $message;
	}
}
