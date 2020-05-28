<?php

use Codeception\Util\HttpCode;

class FrontendCest
{
	public function _before(FunctionalTester $I)
	{
	}

	// tests
	public function viewFrontend(FunctionalTester $I)
	{
		$I->amOnPage('/');
		//$I->seeResponseCodeIs(HttpCode::OK);
		$I->see('Trabajo Fin de Grado');
	}
}
