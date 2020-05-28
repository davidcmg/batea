<?php

/**
 * Dump and Die
 * Inspirado en Laravel: Muestra contenido de la variable y detiene la ejecución.
 * Permite el paso de múltiples parámetros.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
function dd()
{
	$args = func_get_args();
	echo debugStyle();
	foreach ($args as $arg) {
		highlight_string("dd(🔎) \n<?php" . PHP_EOL . var_export($arg, true) . PHP_EOL);
	}
	echo closeDebugStyle();
	tt();
}

/**
 * Muestra un backtrace formateado.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
function tt()
{
	$trace = debug_backtrace();
	if (debug_backtrace()[1]['function'] == 'dd') {
		array_shift($trace);
	}
	$i = sizeof($trace) - 1;
	echo debugStyle();
	echo 'tt(📜) <br>';
	foreach ($trace as $t) {
		echo '#' . $i . ' <font color = "gray">' . basename($t['file']) . '</font>'; // basename($t['file'])
		echo '<font color = "red">(' . $t['line'] . ')</font>: ';
		if (isset($t['class'])) {
			echo '<font color = "brown"> class ' . $t['class'] . ' ⟹</font>';
		}
		echo ' function <font color = "green"><strong>' . $t['function'] . '</strong></font><br />';
		--$i;
	}
	echo closeDebugStyle();
	die();
}

/**
 * Estilo css para formatear las salidas anterires.
 *
 * @author David Campos <davidcmg@uoc.edu>
 *
 * @return string
 */
function debugStyle()
{
	return '<div style=\'color:#0e3c68;font-size:14px;font-family:"Courier New";margin:3em;\'>';
}

/**
 * Cierre de la cadena de estilos.
 *
 * @author David Campos <davidcmg@uoc.edu>
 *
 * @return string
 */
function closeDebugStyle()
{
	return '</div>';
}
