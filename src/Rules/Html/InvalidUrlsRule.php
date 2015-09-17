<?php

namespace whm\Smoke\Rules\Html;

use whm\Html\Document;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;

/**
 * This rules counts the css files that are included in a document. If the number is higher
 * than a given value the test failes.
 */
class InvalidUrlsRule extends StandardRule
{
    protected $contentTypes = array('text/html');

    /**
     * {@inheritdoc}
     */
    protected function doValidation(Response $response)
    {
        $document = new Document($response->getBody());

        $urls = $document->getDependencies($response->getUri());

        $invalidUrls = array();

        foreach ($urls as $url) {
            if (!filter_var((string) $url, FILTER_VALIDATE_URL)) {
                $invalidUrls[] = (string) $url;
            }
        }

        $this->assert(count($invalidUrls) === 0, 'Invalid urls found (' . implode(', ', $invalidUrls) . ').');
    }
}
