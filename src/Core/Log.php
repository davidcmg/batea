<?php

namespace davidcmg\batea\Core;

use davidcmg\batea\Config;
use Exception;

/**
 * Clase para gestionar los logs de la aplicación.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Log
{
	/**
	 * Logs para realizar un debug de la aplicación.
	 *
	 * @param string $data
	 *
	 * @return void
	 */
	public static function debug($data)
	{
		$ts = date('Y-m-d H:i:s');
		$logDir = Config::APP_LOG_DIR;
		$file = Config::APP_ROOT . $logDir . 'log_debug.log';
		if (is_array($data)) {
			$data = implode(PHP_EOL . chr(9), $data);
		}
		$text = '[' . $ts . ']DEBUG:: ' . $data . PHP_EOL;
		self::write($file, $text);
	}

	/**
	 * Añade información a los logs de la aplicación.
	 *
	 * @param string $type define el tipo de información, como podría ser info, error, etc
	 * @param mixed  $data los datos a añadir
	 * @param string $file fichero al que se añadirán los datos
	 *
	 * @return void
	 */
	public static function add($type, $data, $file = null)
	{
		$logDir = Config::APP_LOG_DIR;
		$datetime = date('Y-m-d H:i:s');
		$date = date('Y-m-d');
		if (is_array($data)) {
			$data = implode(';', $data);
		}
		$text = '[' . $datetime . ']' . $type . ':: ' . $data . PHP_EOL;
		if ($file == null) {
			$file = 'log_' . $date . '.log';
		} else {
			$file = 'log_' . $date . '_' . $file . '.log';
		}
		$file = Config::APP_ROOT . $logDir . $file;
		self::write($file, $text);
	}

	/**
	 * Escribe el texto al archivo de log.
	 *
	 * @param string $file
	 * @param string $text
	 *
	 * @return void
	 */
	private static function write($file, $text)
	{
		try {
			file_put_contents($file, $text, FILE_APPEND);
		} catch (Exception $e) {
			throw new Exception(
				'Error logDebug ' . $e->getMessage()
			);
		}
	}
}
