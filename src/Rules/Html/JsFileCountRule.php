<?php

namespace whm\Smoke\Rules\Html;

use whm\Smoke\Http\Document;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rules counts the js files that are included in a document. If the number is higher
 * than a given value the test failes.
 */
class JsFileCountRule implements Rule
{
    private $maxCount;

    /**
     * @param int $maxCount The maximum number of javascript files that are allowed in one html document.
     */
    public function init($maxCount = 10)
    {
        $this->maxCount = $maxCount;
    }

    /**
     * @inheritdoc
     */
    public function validate(Response $response)
    {
        if (!$response->getContentType() === 'text/html') {
            return;
        }

        $document = new Document($response->getBody(), $response->getUri());
        $jsFiles = $document->getExternalDependencies(['js']);

        if (count($jsFiles) > $this->maxCount) {
            throw new ValidationFailedException('Too many (' . count($jsFiles) . ') javascript files were found.');
        }
    }
}
