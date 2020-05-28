<?php

use davidcmg\batea\Core\Http\Response;

class ResponseTest extends \Codeception\Test\Unit
{
	public function testStatusCode()
	{
		$response = new Response();
		$response->setStatusCode('200');
		$this->assertEquals($response->getStatusCode(), 200);
	}

	public function testResponseParams()
	{
		$response = new Response();
		$response->setStatusCode(200);
		$this->assertEquals($response->getStatusCode(), 200);
		$this->assertEquals($response->getStatusCodeText(), 'OK');
		$response->addHeader('key', 'val');
		$this->assertEquals($response->getHeader('key'), 'val');
		$headers = $response->getHeaders();
		$this->stringStartsWith($headers, 'HTTP/1.1 200 OK');
		$response->setContent('<h1>Test</h1>');
		$this->assertEquals($response->getContent(), '<h1>Test</h1>');
	}
}
