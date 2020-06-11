<!-- PROJECT LOGO -->
<br />
<p align="center">
  <a href="https://github.com/davidcmg/batea">
    <img src="public/img/logo.png" alt="Logo" width="80" height="80">
  </a>

  <h2 align="center">Batea<br/></h2>
  <h3 align="center"><strong>B</strong>ackup <strong>A</strong>dministrator <strong>T</strong>ool <strong>E</strong>nd-of-degree <strong>A</strong>pplication</h3>

  <p align="center">
    <a href="https://davidcmg.github.io/batea"><strong>PHPDocs »</strong></a>
    <br />
    <br />
  <strong>Trabajo Final de Grado:</strong>
    <br />
    Gestor de copias de seguridad de rsync para configurar en un Virtual Private Server
    <br />
    <a href="https://davidcmg.com">David Campos</a>
    ·
    <a href="https://uoc.edu">Universitat Oberta de Catalunya</a>
  </p>
</p>


<!-- TABLE OF CONTENTS -->
## Tabla de contenidos
* [Sobre el proyecto](#sobre-el-proyecto)
  * [Realizado con](#realizado-con)
* [Empezando](#empezando)
  * [Prerrequisitos](#prerrequisitos)
  * [Instalación](#instalación)
* [Licencia](#licencia)


<!-- ABOUT THE PROJECT -->
## Sobre el proyecto

El presente proyecto, fue realizado con fines académicos como parte del Trabajo Fin de Grado titulado "Gestor de copias de seguridad de Rsync para configurar en un Virtual Private Server".

### Realizado con

* [PHP](https://www.php.net/)
* [SQLite](https://www.sqlite.org/index.html)
* [rsync](https://rsync.samba.org/)
* [arnapou/jqcron](https://gitlab.com/arnapou/jqcron)
* [phpseclib/phpseclib](https://github.com/phpseclib/phpseclib)
* [phpmailer/phpmailer](https://github.com/PHPMailer/PHPMailer)
* [codeception/codeception](https://github.com/Codeception/Codeception)
* [phpDocumentor](https://github.com/phpDocumentor/phpDocumentor)
* [Bootstrap](https://getbootstrap.com/)
* [Boxicons](https://boxicons.com/)

<!-- GETTING STARTED -->
## Empezando

Puedes conseguir una copia de este proyecto siguiendo los siguientes pasos.

### Prerrequisitos

Este proyecto utiliza Composer para administrar dependencias.  
Se pueden consultar los pasos a seguir para su instalación en el sitio de [composer](https://getcomposer.org/doc/00-intro.md).  

### Instalación
 
1. Clonar el proyecto
```sh
git clone https://github.com/davidcmg/batea.git
```
2. Instalar las dependencias
```sh
cd batea/
composer install --no-dev
```
3. Renombrar el archivo de ``config/Config.sample.php`` a ``config/Config.php`` y establecer los parámetros correspondientes.
```sh
mv config/Config.sample.php config/Config.php
```
4. Configurar el servidor web para que apunte al directorio ``public/``
5. Sería recomendable crear un par de claves para la aplicación.
```sh
cd keys/
ssh-keygen -t rsa
Enter file in which to save the key (/home/youruser/.ssh/id_rsa): id_rsa
Enter passphrase (empty for no passphrase): [en blanco]
```
6. Configurar como propietario del proyecto al usuario del servidor web
```sh
cd ../../
chown www-data:www-data batea/ -R
```
7. Establecer permisos de lectura y escritura a ``storage/log/`` y a ``storage/backups/`` así como al archivo ``storage/db.sqlite``
8. Añadir una entrada al cron del sistema, tiene que ejecuarse como usuario del servidor web, por ejemplo:
```sh
sudo crontab -u www-data -e
* * * * *  /usr/bin/php /full/path/to/project/batea.cron.php >> /dev/null 2>&1
```
El usuario por defecto es ``admin`` con contraseña ``secret``.

## Licencia
Distribuido bajo la licencia MIT. Ver [LICENSE.md](LICENSE.md) para más información.