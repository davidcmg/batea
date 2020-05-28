<?php

use davidcmg\batea\Core\View;

/**
 * Si la aplicación no está configurada como debug en caso de error muestra un
 * texto personalizado.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
function userErrorHandler()
{
	$response = $GLOBALS['response'];
	$code = 500;
	$response->setStatusCode($code);
	$codeText = $response->getStatusCodeText();
	$template = new View(
		'Error.html',
		compact(['code', 'codeText'])
	);
	$response->setContent($template->render());
	header($response->getHeaders());
	$response->print();
}
/**
 * Para otros errores es necesario controlar la función de cierre de php.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
function shutdownError()
{
	if (error_get_last()) {
		userErrorHandler();
	}
}
/**
 * Handler para errores personalizados.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
function customError()
{
	set_error_handler('userErrorHandler');
	register_shutdown_function('shutdownError');
}
/**
 * Redirecciona a otra página
 * Algunos status code:
 *   301: Moved Permanently
 *   302: Found / Moved Temporarily
 *   303: See Other.
 *   403: Forbidden.
 * En caso de que el statuscode sea mayor que 400 muestro el mensaje de error.
 *
 * @author David Campos <davidcmg@uoc.edu>
 *
 * @param string $url
 * @param int    $code
 *
 * @return void
 */
function redirect($url, $code = 302)
{
	$res = $GLOBALS['response'];
	$res->setStatusCode($code);
	header($res->getHeaders());
	if ($code >= 400) {
		$codeText = $res->getStatusCodeText();
		$template = new View(
			'Error.html',
			compact(['code', 'codeText'])
		);
		$res->setContent($template->render());
		$res->print();
	} else {
		header('Location: ' . $url, true, $code);
	}
	exit;
}
/**
 * Controla la página activa en el menú lateral.
 *
 * @author David Campos <davidcmg@uoc.edu>
 *
 * @param string $uri
 *
 * @return string
 */
function isCurrentPage($uri)
{
	$current = $_SERVER['REQUEST_URI'];
	if ($current == $uri && $current == '/admin') {
		echo 'active';
	} else {
		$n = strlen($uri);
		if (substr($current, 0, $n) == $uri && $uri != '/admin') {
			echo 'active';
		}
	}

	return;
}
/**
 * Muestra una alerta con un mensaje pasado entre páginas
 * Se utiliza la cookie 'msg' para el texto y
 * la cookie msg-type para el color de la alerta de bootstrap.
 *
 * @author David Campos <davidcmg@uoc.edu>
 *
 * @return void
 */
function getMessage()
{
	if (isset($_SESSION['msg']) && $_SESSION['msg'] != null) {
		$msg = $_SESSION['msg'];
		$type = $_SESSION['msg-type'];
		unset($_SESSION['msg'], $_SESSION['mst-type']);

		echo <<<EOD
		<div class="alert alert-$type" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
			$msg
		</div>
		EOD;
	}
}
