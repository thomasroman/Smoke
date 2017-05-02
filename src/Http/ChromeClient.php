<?php

namespace whm\Smoke\Http;

use phm\HttpWebdriverClient\Http\Client\Chrome\ChromeClient as phmChromeClient;

class ChromeClient extends phmChromeClient
{
    public function init($host = 'localhost', $port = 4444)
    {
        $this->setWebdriverHost($host);
        $this->setWebdriverPort($port);
    }
}