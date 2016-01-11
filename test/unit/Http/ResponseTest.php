<?php

namespace whm\Smoke\Test\Http;

use Ivory\HttpAdapter\Message\Request;
use Ivory\HttpAdapter\Parser\HeadersParser;
use whm\Html\Uri;
use whm\Smoke\Http\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testResponse()
    {
        $testBody = 'TestBodyWith<strong>special</strong>Chäräctörs';
        $testHeader = HeadersParser::parse(['Header1' => 'Test Header']);
        $testStatus = 200;
        $testUri = new Uri('http://smoke.phmlabs.com');
        $testRequest = new Request($testUri);

        $stream = fopen('data://text/plain,' . $testBody, 'r');

        $response = new Response($stream, $testStatus, array(), ['request' => $testRequest]);

        $this->assertEquals($testBody, $response->getBody());
        $this->assertEquals([], $response->getHeader('Test Header'));
        $this->assertEquals($testStatus, $response->getStatus());

        $this->assertEmpty($response->getContentType());
        $this->assertEquals($testRequest, $response->getRequest());
    }

    public function testContentTypeHeader()
    {
        $stream = fopen('data://text/plain,' . '', 'r');

        $response = new \whm\Smoke\Http\Response($stream, 200, ['Content-Type' => ['application/xml']]);

        $this->assertEquals('application/xml', $response->getContentType());
    }
}
