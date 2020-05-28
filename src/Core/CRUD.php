<?php

namespace davidcmg\batea\Core;

use PDO;

/**
 * CRUD: Funcionalidades básicas de los objetos con la base de datos.
 * Es una clase abstracta que pueden extender otros modelos.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
abstract class CRUD
{
	/**
	 * Nombre de la tabla. Se declara en la clase hijo.
	 *
	 * @var string
	 */
	protected $tableName;

	/**
	 * Conexión con la base de datos. Se de clara en el hijo.
	 *
	 * @var mixed
	 */
	protected $conn;

	/**
	 * Atributos del objeto.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		settype($this->id, 'integer');
	}

	/**
	 * Devuelve un valor del atributo especificado.
	 *
	 * @param string $att
	 *
	 * @return null|mixed
	 */
	public function __get($att)
	{
		if ($this->checkValue($att)) {
			return $this->attributes[$att];
		}

		return null;
	}

	/**
	 * Retorna el ID del modelo.
	 *
	 * @return int
	 */
	public function get_id()
	{
		return intval($this->id);
	}

	/**
	 * Establece el valor de un atributo.
	 * Se controla que la clase hija pueda sobreescribir algún setter.
	 *
	 * @param string $att
	 * @param mixed  $value
	 */
	public function __set($att, $value)
	{
		$method = 'set_' . $att;
		if (method_exists($this, $method)) {
			$this->$method($value);
		} else {
			$this->attributes[$att] = $value;
		}
	}

	/**
	 * Retorna un array con todas las entradas de la tabla.
	 *
	 * @return array
	 */
	public function all()
	{
		$query = 'SELECT * FROM ' . $this->tableName;

		return $this->conn->query($query);
	}

	public function allObjects()
	{
		$class = get_class($this);
		$query = 'SELECT * FROM ' . $this->tableName;

		return $this->conn->query($query, null, null, $class, true);
	}

	/*
	 * Comprueba que existe un atributo.
	 *
	 * @param string $att
	 *
	 * @return boolean
	 */
	private function checkValue($att)
	{
		if (is_array($this->attributes) && array_key_exists($att, $this->attributes)) {
			return true;
		}

		return false;
	}

	/**
	 * Retorna un objeto que coincida con la id.
	 *
	 * @param int $id
	 *
	 * @return object
	 */
	public function getById($id)
	{
		if (is_int($id)) {
			$sql = 'SELECT * FROM ' . $this->tableName . ' WHERE id = :id LIMIT 1';
			$params = ['id' => $id];
			$fetch = PDO::FETCH_CLASS;
			$class = get_class($this);

			return $this->conn->query($sql, $params, $fetch, $class);
		}
	}

	/**
	 * Retorna un array de los elementos de una relacción 1 a n.
	 *
	 * @param string $table   nombre de la tabla en la base de datos
	 * @param string $foreing nombre de la clave foránea
	 *
	 * @return array
	 */
	public function hasMany($table, $foreing)
	{
		$sql = 'SELECT * FROM ' . $table . ' WHERE ' . $foreing . ' = ' . $this->id . ' ORDER BY id DESC';

		return $this->conn->query($sql);
	}

	/**
	 * Retorna el último elemento de una relación 1 a n.
	 *
	 * @param string $table   nombre de la tabla en la base de datos
	 * @param string $foreing nombre de la clave foránea
	 *
	 * @return array
	 */
	public function hasLast($table, $foreing)
	{
		$sql = 'SELECT * FROM ' . $table . ' WHERE ' . $foreing . ' = ' . $this->id . ' ORDER BY id DESC LIMIT 1';

		$result = $this->conn->query($sql);
		if (empty($result)) {
			return null;
		}

		return $this->conn->query($sql)[0];
	}

	/**
	 * Guarda los cambios en la base de datos.
	 *
	 * @return int Devuelve el rowCount de la query SQLite.php
	 */
	public function save()
	{
		$values = $this->attributes;
		$update = '';
		if ($this->checkValue('id')) {
			foreach ($values as $key => $val) {
				if ($key != 'id') {
					$update .= $key . ' = :' . $key . ',';
				}
			}
			$update = rtrim($update, ',');
			$sql = 'UPDATE ' . $this->tableName . ' SET ' . $update . ' WHERE id = :id';

			return $this->conn->query($sql, $values); // return rowCount
		} else {
			return 0;
		}
	}

	/**
	 * Crea una nueva entrada en la base de datos.
	 *
	 * @return int
	 */
	public function create()
	{
		if (!empty($this->attributes)) {
			$names = implode(',', array_keys($this->attributes));
			$values = ':' . implode(',:', array_keys($this->attributes));
			$sql = 'INSERT INTO ' . $this->tableName . '(' . $names . ') VALUES (' . $values . ')';
		} else {
			$sql = 'INSERT INTO ' . $this->tableName . '() VALUES ()';
		}

		return $this->conn->query($sql, $this->attributes);
	}

	/**
	 * Elimina de la base de datos.
	 *
	 * @param int $id
	 *
	 * @return int
	 */
	public function delete($id = null)
	{
		if ($id == null) {
			$id = intval($this->id);
		}
		if (is_int($id)) {
			$sql = 'DELETE FROM ' . $this->tableName . ' WHERE id = :id LIMIT 1';

			return $this->conn->query($sql, ['id' => $id]);
		} else {
			return 0;
		}
	}
}
