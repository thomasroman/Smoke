<?php

namespace whm\Smoke\Rules\Html;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a given html document has a closing html tag </html>.
 */
class ClosingHtmlTagRule implements Rule
{
    public function validate(Response $response)
    {
        if (($response->getStatus() < 300 || $response->getStatus() >= 400) && $response->getContentType() === 'text/html') {
            if (stripos($response->getBody(), '</html>') === false) {
                throw new ValidationFailedException('Closing html tag is missing');
            }
        }
    }
}
