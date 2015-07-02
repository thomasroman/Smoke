<?php

namespace whm\Smoke\Rules\Image;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if the size of an image is bigger than a given max value.
 */
class SizeRule implements Rule
{
    private $maxSize;

    /**
     * @param int $maxSize The maximum size of an image file in kilobytes.
     */
    public function init($maxSize = 100)
    {
        $this->maxSize = $maxSize;
    }

    public function validate(Response $response)
    {
        if (strpos($response->getContentType(), 'image') === false) {
            return;
        }

        $size = strlen($response->getBody()) / 1000;

        if ($size > $this->maxSize) {
            throw new ValidationFailedException('the size of the file is too big (' . $size . ' KB)');
        }
    }
}
