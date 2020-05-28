<?php

use davidcmg\batea\Config;

class ServersCest
{
	// tests
	public function viewServersPage(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/servers');
		$I->see('Añadir servidor');
	}

	public function addServer(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/servers');
		$I->click('Añadir servidor');
		$I->seeCurrentUrlEquals('/admin/servers/create');
		$I->fillField(['name' => 'host'], 'localhost');
		$I->fillField(['name' => 'port'], '22');
		$I->fillField(['name' => 'user'], 'root');
		$I->fillField(['name' => 'password'], '1234');
		$I->click('Crear');
		$I->seeCurrentUrlEquals('/admin/servers/create');
		$I->see('Credenciales incorrectas');

		$insert = <<<EOD
            UPDATE sqlite_sequence SET seq=0 WHERE name = 'servers';
            INSERT INTO "servers"("host","port","user","status") VALUES ('localhost','22','usuario','online');
        EOD;
		$bd = new SQLite3(codecept_root_dir() . Config::APP_DB);
		$bd->exec($insert);
		$I->amOnPage('/admin/servers');
		$I->see('localhost');
		$I->see('online');
	}

	public function viewServer(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/servers');
		$I->click('#btn-view-1');
		$I->seeElement('input', ['value' => '22']);
	}

	public function editServer(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/servers');
		$I->click('#btn-edit-1');
		$I->see('Editar server');
		$I->seeElement('input', ['value' => 'localhost']);
		$I->fillField(['name' => 'host'], '127.0.0.1');
		$I->click('Guardar cambios');
		$I->see('correctamente');
	}

	public function deleteServer(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/servers');
		$I->click('#btn-delete-1');
		$I->wait(1);
		$I->see('¿Deseas borrar este servidor');
		$I->click('Borrar');
		$I->see('Correctamente');
	}
}
