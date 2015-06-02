<?php

namespace whm\Smoke\Rules\Html;

use whm\Smoke\Http\Document;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rules counts the css files that are included in a document. If the number is higher
 * than a given value the test failes.
 */
class CssFileCountRule implements Rule
{
    private $maxCount;

    /**
     * @param int $maxCount The maximum number of css files that are allowed in one html document.
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

        $document = new Document($response->getBody());
        $cssFiles = $document->getExternalDependencies(['css']);

        if (count($cssFiles) > $this->maxCount) {
            throw new ValidationFailedException('Too many (' . count($cssFiles) . ') css files were found.');
        }
    }
}
