<?php

namespace davidcmg\batea\Models;

use davidcmg\batea\Config;
use davidcmg\batea\Core\Auth;
use davidcmg\batea\Core\Cron;
use davidcmg\batea\Core\CRUD;
use davidcmg\batea\Core\Log;
use davidcmg\batea\Core\Shell;
use davidcmg\batea\Core\SQLite;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * =============================================================================
 * CREATE TABLE tasks (
 *     id        INTEGER PRIMARY KEY AUTOINCREMENT
 *                       NOT NULL,
 *     name      TEXT,
 *     server_id INTEGER REFERENCES servers (id) ON DELETE CASCADE
 *                       NOT NULL,
 *     cron      TEXT    NOT NULL,
 *     source    TEXT    NOT NULL,
 *     exclude   TEXT,
 *     depth     INTEGER DEFAULT (1),
 *     email     TEXT
 * );
 * =============================================================================.
 */
/**
 * Clase para definir las tareas de copia de seguridad sobre cada servidor.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Task extends CRUD
{
	/**
	 * Nombre de la tabla en la base de datos.
	 *
	 * @var string
	 */
	protected $tableName = 'tasks';

	/**
	 * Conexión a la base de datos.
	 *
	 * @var SQLite
	 */
	protected $conn;

	/**
	 * Array con los atributos de las tareas.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Instancia de TaskStatus para guardar el estado de la tarea.
	 *
	 * @var TaskStatus
	 */
	protected $status;

	/**
	 * Directorio en dónde se guardarán todas las copias de seguridad.
	 *
	 * @var string
	 */
	protected $taskDir;

	/**
	 * Directorio en el cual se guarda la última copia de seguridad.
	 *
	 * @var string
	 */
	protected $lastDir;

	/**
	 * Instancia del objeto Server al que pertenece la tarea.
	 *
	 * @var Server
	 */
	protected $server;

	/**
	 * Instancia de Shell que se utiliza para ejecutar los comandos.
	 *
	 * @var Shell
	 */
	protected $sh;

	/**
	 * DONE se utiliza como comprobación de la ejecución de un comando.
	 * Tras ejecutar el mismo se ejecuta un echo DONE y se comprueba que la salida
	 * de la ejecución del comando coincida con la constante.
	 */
	const DONE = '"Done\c"';

	/**
	 * Constante para definir el directorio en dónde se guardará la última copia.
	 */
	const LAST = 'last/';

	/**
	 * Constante para definir el directorio en dóde se guardaran todas las copias
	 * de la tarea.
	 */
	const FOLDER_PREFIX = 'task_';

	/**
	 * Constante para definir los directorios en dónde se guardarán el historial
	 * de copias.
	 */
	const PREV_PREFIX = 'files_-';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->conn = new SQLite();
		$this->taskDir = Config::APP_ROOT . Config::APP_BACKUP_DIR . self::FOLDER_PREFIX . $this->id . '/';
		$this->lastDir = $this->taskDir . self::LAST;
		$this->server = new Server();
		$this->server = $this->server->getById(intval($this->server_id));
		$this->sh = new Shell(Config::APP_ROOT . Config::APP_BACKUP_DIR);
		$this->status = new TaskStatus($this->id, date('Y-m-d H:i:s'));
	}

	/**
	 * Se llama a davidcmg\batea\Core\Cron para verificar
	 * si se puede ejecutar una tarea.
	 *
	 * @return void
	 */
	public function check()
	{
		if (Cron::isTaskTime(time(), $this->cron)) {
			$this->run();
		}
	}

	/**
	 * Se crean los directorios para las copias de seguridad.
	 * Con el parámetro -p no muestra error si el directorio ya existe.
	 *
	 * @return boolean
	 */
	public function prepareFolders()
	{
		$command = 'mkdir -p ' . $this->lastDir;
		$pre = $this->taskDir . self::PREV_PREFIX;

		for ($i = $this->depth; $i > 0; --$i) {
			$command .= ' ' . $pre . $i;
		}
		$command .= ' && echo ' . self::DONE;

		$result = $this->sh->exec($command);
		if (!$this->isSuccessful($result)) {
			Log::add('error', 'Error al preparar los directorios para las copias: ' . $command);
			exit();
		}
		Log::add('info', 'Directorios preparados.');

		return true;
	}

	/**
	 * Ejecuta la tarea.
	 * Se comprueba que existe el origen y se rotan el directorio de copias.
	 * Se ejecuta rsync y se crea una entrada de TaskStatus, indicando si se ha
	 * realizado de forma correcta.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->sh->setPath(Config::APP_ROOT . Config::APP_BACKUP_DIR);

		Log::add('info', '**************************************');
		Log::add('info', 'INICIO BACKUP ID ' . $this->id);
		Log::add('info', '*******************');

		if (!$this->server->isOnline()) {
			$this->closeWithError($this->server->connError);
		}

		$ssh = $this->server->getSSH();
		if ($ssh == null) {
			Log::add('error', 'No se pudo obtener una instancia de SSH del servidor.');
			exit();
		}
		$command = '[ -d "' . $this->source . '" ] && echo -e ' . self::DONE;
		$result = $ssh->exec($command);
		if (!$this->isSuccessful($result)) {
			$this->closeWithError('No existe el directorio origen ' . $command);
			exit();
		}

		/*
		 * Preparación de los directorios y rotación de las copias.
		 */
		$this->prepareFolders();
		$depth = (int) $this->depth;
		$logfile = $this->taskDir . 'task_' . $this->id . '.log';
		$tmp = $this->rotateFolders($depth, $logfile);

		/**
		 * Parámetros de rsync.
		 */
		$ssh_options = '-e "ssh -o StrictHostKeyChecking=no -p ' . $this->server->port;
		$ssh_options .= ' -i ' . Config::PRIVATE_KEY . '"';
		$source = $this->server->user . '@' . $this->server->host . ':' . $this->source;
		$target = $this->taskDir . self::PREV_PREFIX . '1/';
		$rsync_options = '-achvzi --progress --timeout=1800 --delete --stats --no-W --chmod=+r';
		$rsync_options .= ' --link-dest=' . $target;
		$rsync_options .= ' --out-format="     >> %o: %f" --log-file=' . $logfile;
		$rsync_options .= ' --partial-dir="' . $tmp . '"';
		$exArr = explode(',', $this->exclude);
		$exclude = '';
		foreach ($exArr as $ex) {
			$exclude .= ' --exclude=' . $ex;
		}

		/*
		 * Inicio de la copia de seguridad
		 */
		Log::add('info', "\tCopia de " . $source . ' a ' . $this->lastDir);
		$command = 'rsync ' . $ssh_options . ' ' . $rsync_options . ' ' . $exclude . ' ' . $source . ' ' . $this->lastDir . ' && echo ' . self::DONE;
		$result = $this->sh->exec($command);
		if ($result == '') {
			Log::add('error', "\tEl comando no recibe ninguna salida");
			$this->status->end = date('Y-m-d H:i:s');
			$this->status->status = 'error';
			$this->status->create();
			exit();
		}

		Log::add('info', "\tResultado ejecución rsync:\n" . $result);

		$this->status->end = date('Y-m-d H:i:s');
		$this->status->status = 'ok';
		$this->status->create();
		if ($this->email) {
			$this->sendEmail($result);
		}

		//	$email = new \SendGrid\Mail\Mail();
		Log::add('info', PHP_EOL . '________________________________________________' . PHP_EOL . '¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯' . PHP_EOL);
	}

	/**
	 * Realiza una rotación de las copias de seguridad.
	 *
	 * @param int    $depth
	 * @param string $logfile
	 *
	 * @return string
	 */
	private function rotateFolders($depth, $logfile)
	{
		for ($i = $depth; $i >= 0; --$i) {
			$folder = $this->taskDir . self::PREV_PREFIX . $i . '/';
			$j = $i + 1;
			$sig = $this->taskDir . self::PREV_PREFIX . $j . '/';
			$tmp = $this->taskDir . 'tmp/';

			if ($i == $depth) { // El archivo más viejo
				$this->sh->exec('mv ' . $folder . ' ' . $tmp);
			} elseif ($i == 0) { // El archivo más nuevo
				$this->sh->exec('mv ' . $logfile . ' ' . $this->lastDir);
				$this->sh->exec('mv ' . $this->lastDir . ' ' . $sig);
			} else {
				$this->sh->exec('mv ' . $folder . ' ' . $sig);
				$this->sh->exec('rm -rf ' . $folder);
			}
		}
		$this->sh->exec('rm -rf ' . $tmp);
		$this->sh->exec('mkdir ' . $tmp);
		Log::add('info', 'Rotación de directorios finalizada');

		return $tmp;
	}

	/**
	 * Restaura una copia de seguridad.
	 *
	 * @param string $depth
	 *
	 * @return void
	 */
	public function restore($depth)
	{
		$ssh_options = '-e "ssh -o StrictHostKeyChecking=no -p ' . $this->server->port;
		$ssh_options .= ' -i ' . Config::PRIVATE_KEY . '"';
		$target = $this->server->user . '@' . $this->server->host . ':' . $this->source;
		if ($depth == '0') {
			$restore = $this->lastDir;
		} else {
			$restore = $this->taskDir . self::PREV_PREFIX . $depth . '/';
		}
		$rsync_options = '-achvzi --progress --timeout=1800 --stats --no-W --chmod=+r';
		Log::add('info', "\tRestaurando copia de seguridad, de " . $restore . ' a ' . $target);
		// Ejecución
		$command = 'rsync ' . $ssh_options . ' ' . $rsync_options . ' ' . $restore . ' ' . $target . ' && echo ' . self::DONE;

		$result = $this->sh->exec($command);
		if ($result == '') {
			Log::add('error', "\tLa restauración falla: El comando no recibe ninguna salida");
			Auth::setMessage('Falla el comando, no se recibe ningúna salida. Comando: <pre>' . $command . '</pre>: <br> <pre>' . $result . '</pre>', 'success');
			redirect('/admin');
			die;
		}
		Log::add('info', "\tResultado ejecución de la restauración de la copia mediante rsync:\n" . $result);

		Auth::setMessage('Restauración completada: <br> <pre>' . $result . '</pre>', 'success');
		redirect('/admin');
	}

	/**
	 * En caso de error, guarda una entrada en el log
	 * y cierra la instancia del status.
	 *
	 * @param string $error
	 *
	 * @return void
	 */
	private function closeWithError($error)
	{
		Log::add('error', $error);
		$this->status->end = date('Y-m-d H:i:s');
		$this->status->status = 'error';
		$this->status->create();
		exit();
	}

	/**
	 * Comprueba el estado de salida de rsync.
	 *
	 * @param string $result
	 *
	 * @return string
	 */
	private function isSuccessful($result)
	{
		$lines = explode("\n", $result);
		$done = str_replace(['\c', '"'], '', self::DONE);
		if (end($lines) == $done) {
			return true;
		}

		return false;
	}

	/**
	 * Devuelve las constantes definidas para utilizar en otras clases.
	 *
	 * @return array
	 */
	public static function getConstants()
	{
		$const = [
			'last' => self::LAST,
			'folder_prefix' => self::FOLDER_PREFIX,
			'prev_prefix' => self::PREV_PREFIX,
		];

		return $const;
	}

	/**
	 * Envía un correo con el reporte de la copia de seguridad.
	 *
	 * @param string $result
	 *
	 * @return void
	 */
	public function sendEmail($result)
	{
		$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
		try {
			$mail->isSMTP();
			$mail->Host = Config::SMTP_SERVER;
			$mail->SMTPAuth = true;
			$mail->Username = Config::SMTP_USER;
			$mail->Password = Config::SMTP_PASSWORD;
			$mail->SMTPSecure = Config::SMTP_SECURE;
			$mail->Port = Config::SMTP_PORT;
			$mail->CharSet = 'UTF-8';
			//Recipients
			$mail->setFrom(Config::EMAIL_FROM, 'Batea Backup');
			$mail->addReplyTo(Config::EMAIL_FROM, 'Information');
			$mail->addAddress($this->email);

			//Content
			$mail->isHTML(true);
			$mail->Subject = 'Reporte tarea > ' . $this->name;
			$mail->Body = 'Tarea \'<strong>' . $this->name . '\'</strong> #' . $this->id . '<br/>';
			$mail->Body .= '<pre>' . $result . '</pre>';

			$mail->send();
			Log::add('info', "\tCorreo enviado correctamente");
		} catch (Exception $e) {
			Log::add('error', "\tMessage could not be sent. Mailer Error: ", $mail->ErrorInfo);
		}
	}
}
