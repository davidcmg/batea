<?php

namespace davidcmg\batea\Controllers;

use davidcmg\batea\Core\View;

/**
 * Controlador para mostrar la página principal de la web.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Index
{
	/**
	 * GET: /
	 * Muestra los textos definidos en su respectiva plantilla.
	 *
	 * @return void
	 */
	public function show()
	{
		$title = 'Batea';
		$subtitle = '<strong>B</strong>ackup <strong>A</strong>dministrator <strong>T</strong>ool <strong>E</strong>nd-of-degree <strong>A</strong>pplication';

		$content = 'David Campos Magdaleno';
		$content .= '<br/>Trabajo Fin de Grado';

		$template = new View(
			'Index.html',
			compact(['title', 'subtitle', 'content'])
		);
		$template->render();
	}
}
