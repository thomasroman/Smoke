<?php

namespace whm\Smoke\Rules\Html;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule will analyze any html document and checks if a given string is contained.
 */
class StringExistsRule implements Rule
{
    private $string;

    /**
     * @param int $string The string that the document must contain
     */
    public function init($string)
    {
        $this->string = $string;
    }

    public function validate(Response $response)
    {
        if ('text/html' !== $response->getContentType()) {
            return;
        }

        if (strpos($response->getBody(), $this->string) === false) {
            throw new ValidationFailedException('The given string (' . $this->string . ') was not found in this document.');
        }
    }
}
