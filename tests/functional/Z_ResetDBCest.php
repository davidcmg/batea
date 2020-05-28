<?php

use davidcmg\batea\Config;
use davidcmg\batea\Core\Shell;

class RestDBCest
{
	// tests
	public function resetDB(FunctionalTester $I)
	{
		$I->amGoingTo('Reestablecer bd original');
		$sh = new Shell(Config::APP_ROOT);
		$sh->exec('rm db.sqlite ');
		$sh->exec('mv db.sqlite.back db.sqlite');
	}
}
