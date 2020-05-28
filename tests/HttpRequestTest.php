<?php

use davidcmg\batea\Core\Http\Request;
use PHPUnit\Framework\TestCase;

/**
 * Test clase Request.
 * En /phpunit.xml se puede configurar los parámetros de $_SERVER.
 */
final class HttpRequestTest extends TestCase
{
	protected $backupGlobalsBlacklist = ['$_SERVER'];

	public function testRequestConstructor()
	{
		$request = new Request();
		$this->assertInstanceOf(Request::class, $request);
	}

	public function testRequestMethods()
	{
		$request = new Request();
		$this->assertEquals($request->getUri(), '/foo/bar');
		$this->assertEquals($request->getMethod(), 'GET');
		$this->assertEquals($request->getQueryString(), 'foo=bar');
		$this->assertContains('bar', $request->getParams());
		$this->assertEquals($request->getIP(), '127.0.0.1');
	}
}
