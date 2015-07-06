<?php

namespace whm\Smoke\Rules\Html;

use whm\Html\Document;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rules counts the css files that are included in a document. If the number is higher
 * than a given value the test failes.
 */
class InvalidUrlsRule implements Rule
{
    /**
     * @inheritdoc
     */
    public function validate(Response $response)
    {
        if (!$response->getContentType() === 'text/html') {
            return;
        }

        $document = new Document($response->getBody());

        $urls = $document->getDependencies($response->getUri());

        $invalidUrls = array();

        foreach ($urls as $url) {
            if (!filter_var((string) $url, FILTER_VALIDATE_URL)) {
                $invalidUrls[] = (string) $url;
            }
        }

        if (count($invalidUrls) > 0) {
            throw new ValidationFailedException('Invalid urls found (' . implode(', ', $invalidUrls) . ').');
        }
    }
}
