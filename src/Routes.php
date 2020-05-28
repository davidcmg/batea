<?php

return [
	'GET' => [
		'/' => ['davidcmg\batea\Controllers\Index', 'show'],
		'/login' => ['davidcmg\batea\Controllers\Login', 'show'],
		'/logout' => ['davidcmg\batea\Controllers\Login', 'logout'],
		'/admin' => ['davidcmg\batea\Controllers\Admin', 'show'],
		/*============ DESCARGAS ============*/
		'/files/log/:folder/:file' => ['davidcmg\batea\Core\Storage', 'log'],
		'/files/log/:folder/:subfolder/:file' => ['davidcmg\batea\Core\Storage', 'log'],
		'/files/backup/:folder/:subfolder' => ['davidcmg\batea\Core\Storage', 'zip'],
		/*============== USERS ==============*/
		'/admin/users' => ['davidcmg\batea\Controllers\Users', 'all'],
		'/admin/users/:id' => ['davidcmg\batea\Controllers\Users', 'show'],
		'/admin/users/create' => ['davidcmg\batea\Controllers\Users', 'new'],
		'/admin/users/:id/edit' => ['davidcmg\batea\Controllers\Users', 'getEdit'],
		/*============= SERVERS =============*/
		'/admin/servers' => ['davidcmg\batea\Controllers\Servers', 'all'],
		'/admin/servers/:id' => ['davidcmg\batea\Controllers\Servers', 'show'],
		'/admin/servers/create' => ['davidcmg\batea\Controllers\Servers', 'new'],
		'/admin/servers/:id/edit' => ['davidcmg\batea\Controllers\Servers', 'getEdit'],
		/*============== TASKS ==============*/
		'/admin/tasks' => ['davidcmg\batea\Controllers\Tasks', 'all'],
		'/admin/tasks/:id' => ['davidcmg\batea\Controllers\Tasks', 'show'],
		'/admin/tasks/create' => ['davidcmg\batea\Controllers\Tasks', 'new'],
		'/admin/tasks/:id/edit' => ['davidcmg\batea\Controllers\Tasks', 'getEdit'],
	],
	'POST' => [
		'/login' => ['davidcmg\batea\Controllers\Login', 'check'],
		/*============== USERS ==============*/
		'/admin/users/create' => ['davidcmg\batea\Controllers\Users', 'create'],
		'/admin/users/:id/edit' => ['davidcmg\batea\Controllers\Users', 'postEdit'],
		'/admin/users/:id/delete' => ['davidcmg\batea\Controllers\Users', 'delete'],
		/*============= SERVERS =============*/
		'/admin/servers/create' => ['davidcmg\batea\Controllers\Servers', 'create'],
		'/admin/servers/:id/edit' => ['davidcmg\batea\Controllers\Servers', 'postEdit'],
		'/admin/servers/:id/delete' => ['davidcmg\batea\Controllers\Servers', 'delete'],
		/*============== TASKS ==============*/
		'/admin/tasks/create' => ['davidcmg\batea\Controllers\Tasks', 'create'],
		'/admin/tasks/:id/edit' => ['davidcmg\batea\Controllers\Tasks', 'postEdit'],
		'/admin/tasks/:id/delete' => ['davidcmg\batea\Controllers\Tasks', 'delete'],
		'/admin/tasks/:id/:depth/restore' => ['davidcmg\batea\Controllers\Tasks', 'restore'],
	],
];
