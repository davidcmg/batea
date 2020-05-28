<?php

namespace Helper;

use davidcmg\batea\Config;
use davidcmg\batea\Core\Shell;
use SQLite3;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Functional extends \Codeception\Module
{
	public function restoreDatabase()
	{
		$bd = new SQLite3(codecept_root_dir() . Config::APP_DB);
		$sh = new Shell(Config::APP_ROOT);
		$sh->exec('cp db.sqlite db.sqlite.back');
		$delete = 'DELETE FROM users';
		$bd->exec($delete);
		$delete = 'DELETE FROM servers';
		$bd->exec($delete);
		$delete = 'DELETE FROM server_status';
		$bd->exec($delete);
		$delete = 'DELETE FROM tasks';
		$bd->exec($delete);
		$delete = 'DELETE FROM task_status';
		$bd->exec($delete);

		$pass = password_hash('secret', PASSWORD_BCRYPT);
		$insert = <<<EOD
		UPDATE sqlite_sequence SET seq=0 WHERE name = 'users';
		UPDATE sqlite_sequence SET seq=0 WHERE name = 'server_status';
		UPDATE sqlite_sequence SET seq=0 WHERE name = 'servers';
		UPDATE sqlite_sequence SET seq=0 WHERE name = 'tasks';
		UPDATE sqlite_sequence SET seq=0 WHERE name = 'task_status';
		INSERT INTO "users"("username","password") VALUES ('admin','$pass');
		EOD;
		$bd->exec($insert);
	}

	/*
	 * Login con usuario determinado determinado en /admin.
	 *
	 * @param type $username
	 * @param type $password
	 */
	public function loginAs($I, $username, $password)
	{
		$I->amOnPage('/login');
		$I->fillField("//input[@type='text']", $username);
		$I->fillField("//input[@type='password']", $password);
		$I->click('login');
	}
}
