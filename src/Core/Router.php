<?php

namespace davidcmg\batea\Core;

use davidcmg\batea\Core\Http\Request;
use davidcmg\batea\Core\Http\Response;

/**
 * Gestión de rutas.
 * Las rutas se definen en un array (Routes.php).
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Router
{
	/**
	 * Request.
	 *
	 * @var davidcmg\batea\Core\Http\Request
	 */
	protected $request;

	/**
	 * Response.
	 *
	 * @var davidcmg\batea\Core\Http\Response
	 */
	protected $response;

	/**
	 * $routes Array con la tabla de rutas.
	 *
	 * @var array
	 */
	protected $routes = [];
	/**
	 * Array con las rutas de un determinado método HTTP.
	 *
	 * @var array
	 */
	protected $methodRoutes = [];
	/**
	 * Ruta actual.
	 *
	 * @var array
	 */
	protected $currentRoute = [];
	/**
	 * Parámetros de la ruta actual.
	 *
	 * @var array
	 */
	protected $params = [];

	public function __construct($routes, Request $request, Response $response)
	{
		$this->routes = $routes;
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * Muestra todas las rutas.
	 *
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Retorna el controlador de una ruta.
	 *
	 * @param string $uri
	 *
	 * @return string
	 */
	private function getRouteController($uri)
	{
		return $this->methodRoutes[$uri][0];
	}

	/**
	 * Retorna la acción del controlador.
	 * Es el método dentro del controlador.
	 *
	 * @param string $uri
	 *
	 * @return string
	 */
	private function getRouteAction($uri)
	{
		return $this->methodRoutes[$uri][1];
	}

	/**
	 * Inicia el router.
	 * Dependiendo del tipo de método http (GET, POST, ...)
	 * se crea un array con las rutas de ese tipo.
	 * Si el código de estado HTTP es distinto de 200, muestra un error personalizado.
	 * En caso contrario, se extrae el controlador, la acción y se hace una llamada.
	 *
	 * @return mixed
	 */
	public function run()
	{
		$this->setRoutesByMethod();
		$this->resolveRoute();
		$code = $this->getHttpCode($this->getCurrentRouteName());
		if ($code != 200) {
			$this->printError($code);
		} else {
			$controllerName = $this->getRouteController($this->getCurrentRouteName());
			$action = $this->getRouteAction($this->getCurrentRouteName());
			$controller = new $controllerName();
			$controller->$action($this->params);
		}
	}

	/**
	 * Devuelve el uri de la ruta actual.
	 *
	 * @return string
	 */
	private function getCurrentRouteName()
	{
		return array_key_first($this->currentRoute);
	}

	/**
	 * Se pasa la url por un pattern para sacar un listado de variables de la misma.
	 *
	 * @see https://stackoverflow.com/a/17372192
	 *
	 * @param string $url
	 * @param array  $pattern
	 *
	 * @return array|false Si no encuentra valores devuelve falso, si no coincide devuelve array vacío
	 */
	private function checkUrlAgainstPattern($url, $pattern)
	{
		// parse $pattern into a regex, and build a list of variable names
		$vars = [];
		$regex = preg_replace_callback(
			'#/:([a-z]+)(?=/|$)#',
			function ($x) use (&$vars) {
				$vars[] = $x[1];

				return '/([^/]+)';
			},
			$pattern
		);

		// check $url against the regex, and populate variables if it matches
		$vals = [];
		if (preg_match("#^{$regex}$#", $url, $x)) {
			foreach ($vars as $id => $var) {
				$vals[$var] = $x[$id + 1];
			}

			return $vals;
		} else {
			return false;
		}
	}

	/**
	 * Resuelve una ruta a partir de una uri.
	 * Busca la petición en la tabla de rutas
	 * y en caso de que tenga parámetros, devuelve los valores.
	 *
	 * @return void
	 */
	private function resolveRoute()
	{
		$requestedUri = $this->request->getUri();
		if (array_key_exists($requestedUri, $this->methodRoutes)) {
			$this->currentRoute[$requestedUri] = $this->methodRoutes[$requestedUri];

			return;
		}

		foreach ($this->methodRoutes as $route => $options) {
			$res = $this->checkUrlAgainstPattern($requestedUri, $route);
			if ($res) {
				$this->currentRoute[$route] = $this->methodRoutes[$route];
				$this->params = $res;
			}
		}

		return;
	}

	/**
	 * Actualiza el array de rutas dependiendo del tipo de método utilizado.
	 * De esta forma, ese trabajará solo con rutas de un tipo (GET p.e.).
	 * Las rutas están definidas en el archivo src/Routes.php.
	 *
	 * @return void
	 */
	private function setRoutesByMethod()
	{
		switch ($this->request->getMethod()) {
		case 'GET':
			$this->methodRoutes = $this->routes['GET'];
			$this->method = 'GET';
		break;
		case 'POST':
			$this->methodRoutes = $this->routes['POST'];
			$this->method = 'POST';
		break;
		case 'DELETE':
			$this->methodRoutes = $this->routes['DELETE'];
			$this->method = 'DELETE';
		break;
		case 'PUT':
			$this->methodRoutes = $this->routes['PUT'];
			$this->method = 'PUT';
		break;
		default:
			$this->printError(405); // Method Not Allowed
			$this->method = null;

			return;
		}
	}

	/**
	 * Muestra códigos de error HTTP en una página de error personalizada.
	 *
	 * @param int $code
	 */
	private function printError($code)
	{
		$this->response->setStatusCode($code);
		$this->response->getStatusCodeText();
		//$this->response->setContent();
		$codeText = $this->response->getStatusCodeText();
		$template = new View(
			'Error.html',
			compact(['code', 'codeText'])
		);
		header($this->response->getHeaders());
		$this->response->setContent($template->render());
		$this->response->print();
		exit;
	}

	/**
	 * Retorna el código HTTP con errores personalizados en caso de estar la ruta
	 * mal formada.
	 *
	 * @param string $uri
	 *
	 * @return int
	 */
	private function getHttpCode($route)
	{
		// Comprueba si la /ruta de la URI existe en el array
		if (!array_key_exists($route, $this->methodRoutes)) {
			return 404; // Not Found
		}
		if ($this->getRouteController($route) == null) {
			return 500; // Internal server error
		}
		if ($this->getRouteAction($route) == null) {
			return 501; // Not Implemented
		}

		return 200; // OK
	}
}
