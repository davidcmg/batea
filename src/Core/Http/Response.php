<?php

namespace davidcmg\batea\Core\Http;

/**
 * Sencilla implementación de HTTP Response.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Response
{
	/**
	 * HTTP Status Codes.
	 *
	 * @var array
	 *
	 * @see https://stackoverflow.com/a/3914021
	 */
	protected $http_codes = [
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		103 => 'Checkpoint',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Switch Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Unordered Collection',
		426 => 'Upgrade Required',
		449 => 'Retry With',
		450 => 'Blocked by Windows Parental Controls',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		509 => 'Bandwidth Limit Exceeded',
		510 => 'Not Extended',
	];

	/**
	 * $statusCode.
	 *
	 * @var int
	 */
	protected $statusCode;

	/**
	 * $statusCodeText.
	 *
	 * @var string
	 */
	protected $statusCodeText;

	/**
	 * $headers.
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * $content Contenido (body).
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * Establece el código de estatus.
	 *
	 * @param int $statusCode
	 *
	 * @return void
	 */
	public function setStatusCode($statusCode)
	{
		$this->statusCode = $statusCode;
		$this->statusCodeText = $this->http_codes[$statusCode];
	}

	/**
	 * Retorna el código de status.
	 *
	 * @return int
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
	}

	/**
	 * Retorna la descripción del código HTTP.
	 *
	 * @return string
	 */
	public function getStatusCodeText()
	{
		return $this->statusCodeText;
	}

	/**
	 * Añade una cabecera.
	 *
	 * @param string $key
	 * @param string $val
	 *
	 * @return void
	 */
	public function addHeader($key, $val)
	{
		$this->headers[$key] = $val;
	}

	/**
	 * Retorna una cabecera.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getHeader($key)
	{
		return $this->headers[$key];
	}

	/**
	 * Retorna todas las cabeceras.
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		$head = 'HTTP/1.1 ' . $this->statusCode . ' ' . $this->statusCodeText . PHP_EOL;
		foreach ($this->headers as $key => $val) {
			$head .= $key . ': ' . $val . PHP_EOL;
		}

		return $head;
	}

	/**
	 * Establece el contenido de la respuesta (HTML).
	 *
	 * @param string $body
	 *
	 * @return void
	 */
	public function setContent($body)
	{
		$this->content = $body;
	}

	/**
	 * Retorna el contenido de la respuesta (HTML).
	 *
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Muestra la respuesta, primero la cabecera de la petición y a continuación
	 * el contenido.
	 *
	 * @return void
	 */
	public function print()
	{
		header($this->getHeaders());
		$this->getContent();
	}

	/**
	 * Permite la descarga de un log de una backup.
	 * El navegador guarda el archivo.
	 *
	 * @param string $file
	 *
	 * @return void
	 */
	public function downloadLog($file)
	{
		header('HTTP/1.1 ' . $this->statusCode . ' ' . $this->statusCodeText);
		header('Content-Description: File Transfer');
		header('Content-Type: text/plain');
		header('Pragma: no-cache');
		header('Content-Length: ' . filesize($file));
		header('Content-Disposition: attachment; filename="' . basename($file) . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
		exit;
	}

	/**
	 * Permite la descarga de un zip.
	 *
	 * @param string $file
	 *
	 * @return void
	 */
	public function downloadBackup($file)
	{
		header('HTTP/1.1 ' . $this->statusCode . ' ' . $this->statusCodeText);
		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Content-Type: text/force-download');
		header('Pragma: no-cache');
		header('Content-Length: ' . filesize($file));
		header('Content-Disposition: attachment; filename="' . basename($file) . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
		unlink($file);
		exit;
	}
}
