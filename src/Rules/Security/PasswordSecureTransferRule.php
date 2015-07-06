<?php

namespace whm\Smoke\Rules\Security;

use Symfony\Component\DomCrawler\Crawler;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule checks if a https request contains any insecure includes via http.
 */
class PasswordSecureTransferRule extends StandardRule
{
    protected $contentTypes = array('text/html');

    protected function doValidation(Response $response)
    {
        $crawler = new Crawler($response->getBody());
        $actionNodes = $crawler->filterXPath('//form[//input[@type="password"]]/@action');

        $url = (string) $response->getUri();

        foreach ($actionNodes as $node) {
            $action = $node->nodeValue;

            if (strpos($action, 'https://') === 0) {
                continue;
            }

            $this->assert(strpos($url, 'https://') !== false, 'Password is transferred insecure using HTTP.');
        }
    }
}
