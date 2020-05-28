<?php

use Codeception\Util\HttpCode;

class LoginCest
{
	public function _before(FunctionalTester $I)
	{
		$I->amGoingTo('restore database');
		$I->restoreDatabase();
	}

	// tests
	public function loginWorks(FunctionalTester $I)
	{
		$I->amGoingTo('login with default user');
		$I->amOnPage('/');
		//$I->seeResponseCodeIs(HttpCode::OK);
		$I->click('Panel de control');
		$I->see('User');
		$I->see('Password');
		$I->amGoingTo('start a session');
		$I->dontSeeCookie('login');
		$I->fillField("//input[@type='text']", 'admin');
		$I->fillField("//input[@type='password']", 'secret');
		$I->click('login');
		//$I->seeResponseCodeIsSuccessful();
		$I->see('Espacio libre');
		$I->seeCookie('BATEA');
		$I->seeCurrentUrlEquals('/admin');
		$I->click('admin');
		$I->click('Logout');
		$I->see('User');
		$I->seeCurrentUrlEquals('/login');
	}

	public function loginWithIncorrectUser(FunctionalTester $I)
	{
		$I->amOnPage('/login');
		$I->fillField("//input[@type='text']", 'foo');
		$I->fillField("//input[@type='password']", 'bar');
		$I->click('login');
		$I->see('Datos incorrectos');
	}

	public function accessToProtectedPages(FunctionalTester $I)
	{
		$I->amOnPage('/admin');
		//$I->seeResponseCodeIs(403);
		$I->see('403 - Forbidden');
		$I->dontSeeCookie('login');
	}
}
