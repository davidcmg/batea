<?php

namespace davidcmg\batea\Models;

use davidcmg\batea\Core\CRUD;
use davidcmg\batea\Core\SQLite;

/**
 * =============================================================================
 * CREATE TABLE server_status (
 *     id        INTEGER  PRIMARY KEY AUTOINCREMENT
 *                        NOT NULL,
 *     server_id INTEGER  REFERENCES servers (id) ON DELETE CASCADE
 *                        NOT NULL,
 *     date      DATETIME NOT NULL,
 *     is_online BOOLEAN  NOT NULL
 *                        DEFAULT (0)
 * );
 * =============================================================================.
 */

/**
 * Clase para guardar un registro del estado de los servidores.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class ServerStatus extends CRUD
{
	/**
	 * Tabla en la base de datos.
	 *
	 * @var string
	 */
	protected $tableName = 'server_status';

	/**
	 * Conector a la base de datos.
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
	 * Constructor.
	 *
	 * @param int    $server_id
	 * @param mixed  $date
	 * @param string $status
	 */
	public function __construct($server_id, $date, $status)
	{
		$this->server_id = $server_id;
		$this->date = $date;
		$this->is_online = $status;
		$this->conn = new SQLite();
	}
}
