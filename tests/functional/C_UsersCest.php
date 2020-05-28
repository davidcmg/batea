<?php

class UsersCest
{
	public function _before(FunctionalTester $I)
	{
	}

	// tests
	public function viewUsersPage(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/users');
		$I->see('Usuarios');
		$I->see('admin', 'td');
	}

	public function addUser(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/users');
		$I->click('Añadir usuario');
		$I->seeCurrentUrlEquals('/admin/users/create');
		$I->fillField(['name' => 'username'], 'david');
		$I->fillField(['name' => 'password'], 'qwerty');
		$I->click('Crear');
		$I->seeCurrentUrlEquals('/admin/users');
		$I->see('david', 'td');
	}

	public function viewUser(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/users');
		$I->click('#btn-view-2');
		$I->seeElement('input', ['value' => 'david']);
	}

	public function editUser(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/users');
		$I->click('#btn-edit-2');
		$I->see('Cambiar contraseña del usuario 2');
		$I->seeElement('input', ['value' => 'david']);
		$I->fillField(['name' => 'password'], '123456');
		$I->click('Cambiar contraseña');
		$I->see('Contraseña modificada correctamente');
		$I->loginAs($I, 'david', '123456');
		$I->amOnPage('/');
		$I->see('david');
	}

	public function deleteUser(FunctionalTester $I)
	{
		$I->loginAs($I, 'admin', 'secret');
		$I->amOnPage('/admin/users');
		$I->click('#btn-delete-2');
		$I->wait(1);
		$I->see('¿Deseas borrar esta fila');
		$I->click('Borrar');
		$I->see('Correctamente');
	}
}
