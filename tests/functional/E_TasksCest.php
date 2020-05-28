<?php

use davidcmg\batea\Config;

class TasksCest
{
	// tests
	public function viewTasksPage(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/tasks');
		$I->see('Añadir tarea');
	}

	public function addTask(FunctionalTester $I)
	{
		$insert = <<<EOD
		UPDATE sqlite_sequence SET seq=0 WHERE name = 'servers';
		INSERT INTO "servers"("host","port","user","status") VALUES ('localhost','22','usuario','online');
	EOD;
		$bd = new SQLite3(codecept_root_dir() . Config::APP_DB);
		$bd->exec($insert);
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/tasks');
		$I->click('Añadir tarea');
		$I->seeCurrentUrlEquals('/admin/tasks/create');
		$I->fillField(['name' => 'name'], 'Tarea de prueba');
		$I->fillField(['name' => 'email'], 'admin@localhost');

		$I->selectOption(['name' => 'server'], '1');
		$I->fillField(['name' => 'source'], '/origen/backup/');
		$I->fillField(['name' => 'depth'], '0');
		$I->fillField(['name' => 'cron'], '* * * * *');
		$I->fillField(['name' => 'exclude'], '.gitignore');
		$I->click('Crear');
		$I->seeCurrentUrlEquals('/admin/tasks');
		$I->see('Tarea Tarea de prueba creada correctamente');
	}

	public function viewTask(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/tasks');
		$I->click('#btn-view-1');
		$I->seeElement('input', ['value' => 'Tarea de prueba']);
	}

	public function editTask(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/tasks');
		$I->click('#btn-edit-1');
		$I->see('Editar tarea');
		$I->seeElement('input', ['value' => 'Tarea de prueba']);
		$I->fillField(['name' => 'email'], 'mail@localhost');
		$I->click('Guardar');
		$I->see('correctamente');
	}

	public function deleteTask(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/tasks');
		$I->click('#btn-delete-1');
		$I->wait(1);
		$I->see('¿Deseas borrar esta tarea');
		$I->click('Borrar');
		$I->see('Correctamente');
	}
}
