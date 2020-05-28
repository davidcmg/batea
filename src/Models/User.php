<?php

namespace davidcmg\batea\Models;

use davidcmg\batea\Core\CRUD;
use davidcmg\batea\Core\SQLite;

/**
 * =============================================================================
 * CREATE TABLE users (
 *     id       INTEGER NOT NULL
 *                      PRIMARY KEY AUTOINCREMENT,
 *     username TEXT    NOT NULL
 *                      UNIQUE,
 *     password TEXT    NOT NULL
 * );
 * =============================================================================.
 */
/**
 * Modelo para los usuarios de la aplicación.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class User extends CRUD
{
	/**
	 * Nombre de la tabla en la base de datos.
	 *
	 * @var string
	 */
	protected $tableName = 'users';

	/**
	 * Conexión a la base de datos.
	 *
	 * @var object
	 */
	protected $conn;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->conn = new SQLite();
	}

	/**
	 * Establece la contraseña.
	 * Este método sobreescribe al método CRUD.
	 * Es necesario controlar la contraseña, ya que PDO, al generar el objeto
	 * hace hash del propio hash.
	 *
	 * @param string $pass
	 *
	 * @return void
	 */
	protected function set_password($pass)
	{
		if (self::isHash($pass)) {
			$this->attributes['password'] = $pass;
		} else {
			$this->attributes['password'] = password_hash($pass, PASSWORD_DEFAULT);
		}
	}

	/**
	 * Comprueba que la contraseña es un hash.
	 *
	 * @param string $pass
	 *
	 * @return boolean
	 */
	private function isHash($pass)
	{
		if (password_get_info($pass)['algoName'] == 'bcrypt') {
			return true;
		}

		return false;
	}
}
