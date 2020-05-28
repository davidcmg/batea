<?php

use davidcmg\batea\Core\Http\Request;
use davidcmg\batea\Core\Http\Response;
use davidcmg\batea\Core\Router;
use PHPUnit\Framework\TestCase;

final class TouterTest extends TestCase
{
	private $routes = [
		'GET' => [
			'/' => ['davidcmg\batea\Controllers\Page', 'show'],
			'/login' => ['davidcmg\batea\Controllers\Login', 'show'],
		],
		'POST' => [
			'/login' => ['davidcmg\batea\Controllers\Login', 'check'],
		],
	];

	public function testRouterConstructor()
	{
		$request = new Request();
		$response = new Response();
		$router = new Router($this->routes, $request, $response);
		$this->assertInstanceOf(Router::class, $router);
	}

	public function testRouterMethods()
	{
		$request = new Request();
		$response = new Response();
		$router = new Router($this->routes, $request, $response);
		$this->assertEquals($router->getRoutes(), $this->routes);
	}
}
