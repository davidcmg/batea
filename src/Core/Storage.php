<?php

namespace davidcmg\batea\Core;

use davidcmg\batea\Config;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

/**
 * Clase para manejar los archivos en el directorio de almacenamiento.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Storage
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		Auth::isLogged();
	}

	/**
	 * Permite la descarga de un archivo .log de storage/backups/
	 * Se controla el nombre para evitar sql injection.
	 *
	 * @param array $params parámetros de la ruta (Routes.php)
	 *
	 * @return mixed
	 */
	public function log($params)
	{
		$fileName = implode('/', $params);

		if (preg_match('/^[\/,\w,\s-]+\.log$/', $fileName)) {
			$file = Config::APP_ROOT . Config::APP_BACKUP_DIR . $fileName;
			if (file_exists($file)) {
				$response = $GLOBALS['response'];

				return $response->downloadLog($file);
			}
		}
		redirect('/404', 404);
		die(404);
	}

	/**
	 * Recorre recursivamente los directorios para guardar el contenido en un zip.
	 *
	 * @see https://www.php.net/manual/en/class.ziparchive.php.
	 *
	 * @param string     $folder
	 * @param ZipArchive $zipFile
	 */
	private static function folderToZip($source, $zipFile)
	{
		if (is_dir($source)) {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($source),
				RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ($files as $file) {
				$filename = substr($file, strrpos($file, '/') + 1);
				if ($filename == '.' || $filename == '..') {
					continue;
				}

				$file = realpath($file);

				if (is_dir($file)) {
					$zipFile->addEmptyDir(str_replace($source . '/', '', $file . '/'));
				} elseif (is_file($file)) {
					$zipFile->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
				}
			}
		} elseif (is_file($source)) {
			$zipFile->addFromString(basename($source), file_get_contents($source));
		}

		return;
	}

	/**
	 * Descarga un directorio.
	 * Se comprueba en nombre para evitar inyectiones en la URL.
	 *
	 * @see https://www.php.net/manual/en/class.ziparchive.php.
	 *
	 * @param array $params parámetros de la ruta (Routes.php)
	 *
	 * @return void
	 */
	public static function zip($params)
	{
		if (!extension_loaded('zip')) {
			Auth::setMessage('No se dispone de la extensión zip', 'danger');
			redirect('/admin');
			die();
		}

		$source = Config::APP_ROOT . Config::APP_BACKUP_DIR . $params['folder'] . '/' . $params['subfolder'];
		$outZipPath = Config::APP_ROOT . Config::APP_BACKUP_DIR . $params['folder'] . '/tmp/' . implode('_', $params) . '.zip';

		if (preg_match('/^[\/,\w,\s-]+[^*?"<>|:]*$/', $source)) {
			$z = new ZipArchive();
			$z->open($outZipPath, ZIPARCHIVE::CREATE);
			self::folderToZip($source, $z);
			$z->close();

			$response = $GLOBALS['response'];

			return $response->downloadBackup($outZipPath);
		}
		redirect('/404', 404);
		die(404);
	}
}
