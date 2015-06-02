<?php

namespace whm\Smoke\Test\Http;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testRequest()
    {
        $uri = 'http://smoke.phmlabs.com';
        $request = new \whm\Smoke\Http\Request($uri);

        $this->assertEquals($uri, $request->getUri());
    }
}
