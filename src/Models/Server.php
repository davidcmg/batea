<?php

namespace davidcmg\batea\Models;

use davidcmg\batea\Config;
use davidcmg\batea\Core\CRUD;
use davidcmg\batea\Core\Log;
use davidcmg\batea\Core\SQLite;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2 as SSH2;

define('NET_SSH2_LOGGING', 2);
/**
 * =============================================================================
 * CREATE TABLE servers (
 *     id        INTEGER NOT NULL
 *                       PRIMARY KEY AUTOINCREMENT,
 *     user      TEXT    NOT NULL,
 *     host      TEXT    NOT NULL,
 *     port      NUMERIC NOT NULL,
 *     status    TEXT,
 *     is_online BOOLEAN
 * );
 * =============================================================================.
 */

/**
 * Clase que define los servidores a los que se conectará la aplicación para
 * realizar las copias de seguridad.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Server extends CRUD
{
	/**
	 * Tabla en la base de datos.
	 *
	 * @var string
	 */
	protected $tableName = 'servers';

	/**
	 * Conexión a la base de datos.
	 *
	 * @var object
	 */
	protected $conn;

	/**
	 * Atributos.
	 *
	 * @var array
	 */
	protected $attributes = [];
	/**
	 * Error de conexión.
	 */
	public $connError;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->conn = new SQLite();
	}

	/**
	 * Crea y retorna una instancia de phpseclib\Net\SSH2.
	 *
	 * @return object|null
	 */
	public function getSSH()
	{
		$ssh = new SSH2($this->host, $this->port);
		$key = new RSA();

		$key->loadKey(file_get_contents(Config::PRIVATE_KEY));

		if (!$ssh->login($this->user, $key)) {
			Log::add('error', 'No es posible acceder mediante ssh con las credenciales actuales. ' . $ssh->getLastError);

			return null;
		}

		return $ssh;
	}

	/**
	 * Comprueba que el servidor está en línea.
	 *
	 * @return boolean
	 */
	public function isOnline()
	{
		set_error_handler(function () {
			return true;
		});
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		$connection = socket_connect($socket, $this->host, $this->port);
		if ($connection) {
			restore_error_handler();
			socket_close($socket);

			return true;
		}
		$this->connError = socket_strerror(socket_last_error($socket));
		socket_close($socket);
		restore_error_handler();

		return 0;
	}

	/**
	 * Establece una conexión SSH con contraseña en texto.
	 *
	 * @param string $pass
	 *
	 * @return object
	 */
	public function loginWithPass($pass)
	{
		$ssh = new SSH2($this->host, $this->port);

		// En caso de que no se efectúe la conexión, muestra un error E_USER_WARNING que capturo
		set_error_handler(function () {
			return true;
		});
		if (!$ssh->login($this->user, $pass)) {
			if ($ssh->isConnected()) {
				$this->connError = 'Credenciales incorrectas';
			} else {
				$this->connError = 'No se puede establecer la conexión';
			}
			restore_error_handler();

			return false;
		}
		restore_error_handler();

		return $ssh;
	}

	/**
	 * Copia la clave pública al servidor para poder acceder en el futuro sin contraseña.
	 *
	 * @param SSH2 $ssh
	 *
	 * @return void
	 */
	public function copyPublicKey(SSH2 $ssh)
	{
		$pub_key = file_get_contents(Config::PUBLIC_KEY);
		$command = 'echo "' . $pub_key . '" >> ~/.ssh/authorized_keys; echo $?';

		return $ssh->exec($command);
	}

	/**
	 * Actualiza el estado del servidor.
	 *
	 * @param bool $online
	 *
	 * @return void
	 */
	public function addStatus($online)
	{
		$last = null;
		if ($this->status == 'online') {
			$last = true;
		} else {
			$last = false;
		}
		if ($last != $online) {
			if ($online == true) {
				$this->status = 'online';
				$this->save();
			} else {
				$this->status = 'offline';
				$this->save();
			}
			$status = new ServerStatus($this->id, date('Y-m-d H:i:s'), $online);
			$status->create();
			Log::debug('cambio de estado: ' . $this->host . ' ' . $this->status);
		}
	}
}
