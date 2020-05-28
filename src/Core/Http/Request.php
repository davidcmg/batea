<?php

namespace davidcmg\batea\Core\Http;

/**
 * Sencilla implementación de HTTP Request.
 *
 * @author David Campos <davidcmg@uoc.edu>
 *
 * @see https://developer.mozilla.org/es/docs/Web/HTTP/Messages.
 */
class Request
{
	/**
	 * Identificador.
	 *
	 * @var string
	 */
	protected $uri;

	/**
	 * Método utilizado (GET, POST, etc.).
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * Cadena de consulta.
	 *
	 * @var string
	 */
	protected $queryString;

	/**
	 * Cadena de consulta $queryString a array().
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * La dirección IP desde la cual se está accediendo.
	 *
	 * @var string
	 */
	protected $IP;

	/**
	 * $_POST.
	 *
	 * @var array
	 */
	protected $post = [];

	/**
	 * Constructor de Request.
	 */
	public function __construct()
	{
		$this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->queryString = $_SERVER['QUERY_STRING'];
		parse_str($this->queryString, $this->params);
		$this->IP = $_SERVER['REMOTE_ADDR'];
		$this->post = $_POST;
	}

	/**
	 * Devuelve la URI.
	 *
	 * @return string
	 */
	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * Devuelve el método (GET, POST, ...).
	 *
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Devuelve los parámetros.
	 *
	 * @return string
	 */
	public function getQueryString()
	{
		return $this->queryString;
	}

	/**
	 * Devuelve los parámetros en formato array.
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Retorna la dirección IP desde la cual se está accediendo;.
	 *
	 * @return string
	 */
	public function getIP()
	{
		return $this->IP;
	}
}
