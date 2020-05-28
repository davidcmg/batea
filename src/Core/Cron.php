<?php

namespace davidcmg\batea\Core;

/**
 * Clase para gestionar las fechas en fromato CRON.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Cron
{
	/**
	 * Definición de las unidades de tiempo con el formato de la función date
	 * de PHP.
	 */
	const UNIT = [
		'minuto' => 'i',
		'hora' => 'H',
		'dia' => 'd',
		'mes' => 'm',
		'diaSemana' => 'w',
	];

	/**
	 * Rangos de tiempo según la unidad y el formato.
	 */
	const RANGE = [
		'minuto' => '0-59',
		'hora' => '0-23',
		'dia' => '1-31',
		'mes' => '1-12',
		'diaSemana' => '0-6',
	];

	/**
	 * Comprueba que una tarea se puede ejecutar según el cron.
	 * Para cada tarea guardo la programación en variables separadas, representando
	 * cada una, una parte del cron, con el siguiente formato:.
	 *
	 * 		.--------------- minuto (0-59)
	 * 		|  .------------ hora (0-23)
	 * 		|  |  .--------- día del mes (1-31)
	 * 		|  |  |  .------ mes (1-12) o jan,feb,mar,apr,may,jun,jul... (meses en inglés)
	 * 		|  |  |  |  .--- día de la semana (0-6) (domingo=0 ó 7) o sun,mon,tue,wed,thu,fri,sat (días en inglés)
	 * 		|  |  |  |  |
	 * 		*  *  *  *  *
	 *
	 * A continuación se define un array con el formato de cada variable, para
	 * extraerlo de la hora actual y poder hacer la comparación.
	 *
	 * @author David Campos <davidcmg@uoc.edu>
	 *
	 * @param array $cron Array con la fecha programada
	 * @param int   $time Fecha actual
	 *
	 * @return boolean
	 */
	public static function isTaskTime($time, $cron)
	{
		list($minuto, $hora, $dia, $mes, $diaSemana) = explode(' ', $cron);

		foreach (self::UNIT as $unit => $format) {
			$val = $$unit; // Se recorre $cron[]
			$values = [];

			if (strpos($val, '/') != false) {
				$values = self::intervals($unit, $val, $values);
			} else {
				$values = self::list($val);
			}
			if (!in_array(date($format, $time), $values) && (strval($val) != '*')) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Intervalos separados con una barra.
	 * Intervalos definidos con '/'
	 * Como por ejemplo:
	 * 0-59/5
	 * | |  |
	 * | |  .---- interval
	 * | .------- stop
	 * .--------- start.
	 *
	 * @param string $unit   la unidad de tiempo definida en UNIT
	 * @param string $val    el rango de tiempo que corresponde con la unidad
	 * @param array  $values valores que se van almacenando en el array
	 *
	 * @return array
	 */
	private static function intervals($unit, $val, $values)
	{
		list($rank, $steps) = explode('/', $val);

		if ($rank == '*') {
			$rank = self::RANGE[$unit];
		}
		list($start, $stop) = explode('-', $rank);
		for ($i = $start; $i <= $stop; $i = $i + $steps) {
			$values[] = $i;
		}

		return $values;
	}

	/**
	 * Valores separados por comas.
	 * Para cada valor se calcula el inicio y el final.
	 *
	 * @param string $val el rango de tiempo que corresponde con la unidad
	 *
	 * @return array
	 */
	private static function list($val)
	{
		// Intervalos separados por comas
		$items = explode(',', $val);
		// Para cada valor
		foreach ($items as $item) {
			// Si están separados por un guión -
			if (strpos($item, '-') != false) {
				list($init, $end) = explode('-', $item);
				for ($i = $init; $i <= $end; ++$i) {
					$values[] = $i;
				}
			} else {
				$values[] = $item;
			}
		}

		return $values;
	}
}
