<?php

namespace davidcmg\batea\Core;

use davidcmg\batea\Config;
use Exception;
use PDO;
use PDOException;

/**
 * Clase para manejar la conexión y las consultas a la base de datos SQLite.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class SQLite
{
	/**
	 * Archivo SQLite. Ruta completa.
	 *
	 * @var String
	 */
	protected $dbFile;

	/**
	 * Instancia de la base de datos.
	 *
	 * @var object
	 */
	protected $db;

	/**
	 * Constructor para SQLite
	 * PDO::ATTR_ERRMODE: Reporte de errores
	 * PDO::ERRMODE_EXCEPTION: Lanza excepciones.
	 */
	public function __construct()
	{
		$dbFile = Config::APP_ROOT . Config::APP_DB;
		if (!is_file($dbFile)) {
			throw new Exception('No se encuentra ' . $dbFile);
		}
		$this->dbFile = $dbFile;

		try {
			$this->db = new PDO('sqlite:' . $this->dbFile);

			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			// Por defecto en SQLite3 tiene las restricciones de clave externa deshabilitadas.
			$this->db->exec('PRAGMA foreign_keys = ON;');
		} catch (PDOException $e) {
			throw new Exception(
				'Error SQLite ' . $e->getMessage()
			);
		}
	}

	/**
	 * Prepara la conuslta y vincula los parámetros.
	 * La función retorna un PDOStatement con la consulta preparada.
	 *
	 * @param string $query
	 * @param array  $params
	 *
	 * @return mixed
	 */
	private function process($query, $params = null)
	{
		try {
			$stmnt = $this->db->prepare(($query));
			if ($params != '' || $params != null) {
				foreach ($params as $key => $val) {
					$stmnt->bindValue(':' . $key, $val);
				}
			}

			return $stmnt;
		} catch (PDOException $e) {
			throw new Exception('Error PDO ' . $e->getMessage());
		}
	}

	/**
	 * Ejecuta una consulta.
	 *
	 * @param string $query  Sentencia SQL
	 * @param array  $params
	 * @param int    $fetch  tipo de extracción, por defecto es 2
	 * @param string $class  clase de la que se queire devolver el objeto
	 *
	 * @return mixed
	 */
	public function query($query, $params = null, $fetch = PDO::FETCH_ASSOC, $class = null, $flag = null)
	{
		$stmnt = $this->process($query, $params);
		if ($stmnt->execute() == true) {
			$type = strtoupper(explode(' ', $query)[0]);
			switch ($type) {
				case 'SELECT':

					if ($fetch == PDO::FETCH_CLASS) {
						$stmnt->setFetchMode(PDO::FETCH_CLASS, $class);

						return $stmnt->fetch();
					}
					if ($flag == null) {
						return $stmnt->fetchAll($fetch);
					} else {
						return $stmnt->fetchAll(PDO::FETCH_CLASS, $class);
					}

				break;
				case 'INSERT':

					return $this->db->lastInsertId();
				case 'UPDATE':
				case 'DELETE':
				default:
				return $stmnt->rowCount();
			}
		}
	}
}
