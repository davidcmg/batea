<?php

namespace davidcmg\batea\Core;

use Exception;

/**
 * Clase para obtener acceso al intérprete de comandos de GNU/Linux.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Shell
{
	/**
	 * Directorio sobre el que se iniciará.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Error tras la ejecución.
	 *
	 * @var string
	 */
	public $error;

	/**
	 * Salida tras la ejecución.
	 *
	 * @var string
	 */
	public $out;

	/**
	 * Código de salida tras la ejecución.
	 *
	 * @var string
	 */
	public $exitCode;

	/**
	 * Constructor.
	 *
	 * @param string $path
	 */
	public function __construct($path = null)
	{
		$this->path = $path;
	}

	/**
	 * Establece la ruta en dónde se ejecutará el intérprete.
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function setPath($path)
	{
		if (is_dir($path)) {
			$this->path = $path;
		} else {
			throw new Exception('El directorio ' . $path . ' no existe');
		}
	}

	/**
	 * Retorna la ruta en dónde se ejecutará el intérprete.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Ejecuta el comando en el intérprete.
	 *
	 * @param string $command
	 *
	 * @return string
	 */
	public function exec($command)
	{
		$descriptorspec = [
			0 => ['pipe', 'r'],  // stdin es una tubería usada por el hijo para lectura
			1 => ['pipe', 'w'],  // stdout es una tubería usada por el hijo para escritura
			2 => ['pipe', 'w'],  // stderr es un fichero para escritura
		];
		$pipes = [];
		$env = null;
		$process = proc_open($command, $descriptorspec, $pipes, $this->path, $env);

		if (is_resource($process)) {
			$this->out = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			$this->err = stream_get_contents($pipes[2]);
			fclose($pipes[2]);

			// Es importante que se cierren todas las tubería antes de llamar a
			// proc_close para evitar así un punto muerto
			$this->exitCode = proc_close($process);

			return $this->out;
		}
		Log::add('error', 'Error al procesar el comando ' . $command);
		exit();
	}
}
