<?php

namespace whm\Smoke\Rules\Html;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule calculates the size of a html document. If the document is bigger than a given value
 * the test will fail.
 */
class SizeRule implements Rule
{
    private $maxSize;

    /**
     * @param int $maxSize The maximum size of a html file in kilobytes.
     */
    public function init($maxSize = 200)
    {
        $this->maxSize = $maxSize;
    }

    public function validate(Response $response)
    {
        if ('text/html' !== $response->getContentType()) {
            return;
        }

        $size = strlen($response->getBody()) / 1000;
        if ($size > $this->maxSize) {
            throw new ValidationFailedException('The size of this html file is too big (' . $size . ' KB)');
        }
    }
}
