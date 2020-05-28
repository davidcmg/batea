<?php

namespace davidcmg\batea\Core;

use davidcmg\batea\Config;
use Exception;

/**
 * Clase para renderizar las vistas
 * Se carga una plantilla y se añade el texto de las variables.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class View
{
	/**
	 * $path ruta a la localización de las platnillas.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * $template Nombre de la plantilla.
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * $vars Contenido de las variables que se mostrarán en la plantilla.
	 *
	 * @var array
	 */
	protected $vars = [];

	/**
	 * Constructor.
	 *
	 * @param string $template
	 * @param array  $vars
	 */
	public function __construct($template, $vars = null)
	{
		$this->path = Config::APP_ROOT . 'templates/';
		$this->template = $template;
		$this->vars = $vars;
	}

	/**
	 * Renderiza la plantilla.
	 *
	 * @return void
	 */
	public function render()
	{
		$out = $this->checkFile($this->template);
		$out = $this->loadPartials($out);
		$out = $this->feed($out);
		eval(' ?>' . $out . '<?php ');
		die;
	}

	/**
	 * Comprovación de que el archivo de la plantilla existe.
	 * Devuelve el contenido del archivo.
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	private function checkFile($template)
	{
		if (file_exists($this->getTemplatePath($template))) {
			return file_get_contents($this->getTemplatePath($template));
		} else {
			throw new Exception(
				'Template file does not exist: ' . $template
			);
		}
	}

	/**
	 * Añade los datos a la plantilla y devuelve el contenido con los nuevos datos.
	 *
	 * @param string $tpl
	 *
	 * @return string
	 */
	private function feed($tpl)
	{
		foreach ($this->vars as $key => $val) {
			$tpl = str_replace('[@' . $key . ']', $val, $tpl);
		}

		return $tpl;
	}

	/**
	 * Carga en la plantilla el contenido de los parciales.
	 *
	 * @param string $tpl
	 *
	 * @return string
	 */
	private function loadPartials($tpl)
	{
		$partials = [];

		preg_match_all('/\[\#([^\]]+)]/', $tpl, $partials); //     /\[\#([^\]]+)]/
		$i = 0;
		foreach ($partials[1] as $partialTpl) {
			if ($partContent = $this->checkFile($partialTpl)) {
				$tpl = str_replace($partials[0][$i], $partContent, $tpl);
			}
			++$i;
		}

		return $tpl;
	}

	/**
	 * Retorna la ruta + en nombre del fichero.
	 *
	 * @param string $tpl
	 *
	 * @return string
	 */
	private function getTemplatePath($tpl)
	{
		return $this->path . $tpl;
	}
}
