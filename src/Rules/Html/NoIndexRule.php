<?php

namespace whm\Smoke\Rules\Html;

use Symfony\Component\DomCrawler\Crawler;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule checks if the no-index robots meta tag is not set.
 */
class NoIndexRule extends StandardRule
{
    protected $contentTypes = array('text/html');

    protected function doValidation(Response $response)
    {
        if ($response->getStatus() >= 300) {
            return;
        }

        $crawler = new Crawler($response->getBody());
        $metaTags = $crawler->filterXPath("//meta[@name='robots']/@content");

        foreach ($metaTags as $metaTag) {
            $this->assert(strpos($metaTag->nodeValue, 'no-index') === false, 'A meta tag "robots" with the value "no-index" was found');
        }
    }
}
