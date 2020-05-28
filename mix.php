<?php
/**
 * genera la ruta para los recursos con la id de versión generada por mix.
 *
 * @see https://www.sitepoint.com/use-laravel-mix-non-laravel-projects/.
 */
if (!function_exists('mix')) {
	/**
	 * Get the path to a versioned Mix file.
	 *
	 * @param string $path
	 * @param string $manifestDirectory
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	function mix($path)
	{
		$rootPath = dirname($_SERVER['DOCUMENT_ROOT']);

		if (!file_exists($manifestPath = ($rootPath . '/mix-manifest.json'))) {
			throw new Exception('The Mix manifest does not exist.');
		}

		$manifest = json_decode(file_get_contents($manifestPath), true);

		$path = '/public/' . $path;

		if (!array_key_exists($path, $manifest)) {
			throw new Exception(
				"Unable to locate Mix file: {$path}. Please check your " .
				'webpack.mix.js output paths and try again.'
			);
		}

		return str_replace('/public', '', $manifest[$path]);
	}
}
