<?php

namespace davidcmg\batea;

/**
 * Fichero de configuración.
 *
 * @author David Campos <davidcmg@uoc.edu>
 */
class Config
{
	/**
	 * Nombre de la aplicación.
	 */
	const APP_NAME = 'BATEA';

	/**
	 * Activa o desactiva e modo debug.
	 */
	const APP_DEBUG = false;

	/**
	 * Directorio de instalación del proyecto.
	 * Importante la barra final, que indica que es un directorio.
	 */
	const APP_ROOT = '/full/path/to/project/';

	/**
	 * Nombre del archivo SQLite3.
	 */
	const APP_DB = 'storage/db.sqlite';

	/**
	 * Duración de la sesión en milisegundos.
	 */
	const SESSION_LIFETIME = 1800000;

	/**
	 * Par de claves, se utilizan para obtener acceso a los servidores remotos
	 * sin necesidad de especificar una contraseña.
	 * Archivo con la clave pública.
	 * Archivo con la clave privada.
	 */
	const PUBLIC_KEY = '/full/path/to/project/keys/id_rsa.pub';
	const PRIVATE_KEY = '/full/path/to/project/keys/id_rsa';

	/**
	 * Directorio para guardar los logs.
	 * Importante la barra final, que indica que es un directorio.
	 */
	const APP_LOG_DIR = 'storage/logs/';

	/**
	 * Directorio para guardar las copias de seguridad.
	 * Importante la barra final, que indica que es un directorio.
	 */
	const APP_BACKUP_DIR = 'storage/backups/';

	/**
	 * Zona horaria de la aplicación.
	 */
	const APP_TIMEZONE = 'Europe/Madrid';

	/**
	 * Parámetros SMTP para el envio de correos.
	 */
	const SMTP_SERVER = 'smptp.example.com';
	const SMTP_SECURE = 'tls';
	const SMTP_PORT = 587;
	const SMTP_USER = 'user';
	const SMTP_PASSWORD = 'password';
	const EMAIL_FROM = 'sender@email.com';
}
