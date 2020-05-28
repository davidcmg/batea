<?php

namespace davidcmg\batea\Models;

use davidcmg\batea\Core\CRUD;
use davidcmg\batea\Core\SQLite;

/**
 * =============================================================================.
 *
 * CREATE TABLE task_status (
 *     id      INTEGER  PRIMARY KEY AUTOINCREMENT
 *                      NOT NULL,
 *     task_id INTEGER  REFERENCES tasks (id) ON DELETE CASCADE,
 *     start   DATETIME,
 *     [end]   DATETIME,
 *     status  TEXT
 * );
 * =============================================================================
 */
/**
 * Clase para guardar un registro del estado de los servidores.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class TaskStatus extends CRUD
{
	/**
	 * Nombre de la tabla en la base de datos.
	 *
	 * @var string
	 */
	protected $tableName = 'task_status';

	/**
	 * Conexión a la base de datos.
	 *
	 * @var object
	 */
	protected $conn;

	/**
	 * Atributos del modelo.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Constructor.
	 *
	 * @param int   $task_id
	 * @param mixed $start
	 */
	public function __construct($task_id, $start)
	{
		$this->task_id = $task_id;
		$this->start = $start;
		$this->conn = new SQLite();
	}
}
