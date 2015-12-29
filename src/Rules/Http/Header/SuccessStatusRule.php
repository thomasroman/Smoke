<?php

namespace whm\Smoke\Rules\Http\Header;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if the http status code of a request is less than 400.
 */
class SuccessStatusRule implements Rule
{
    private $maxStatusCode;

    public function init($maxStatusCode)
    {
        $this->maxStatusCode = $maxStatusCode;
    }

    public function validate(Response $response)
    {
        if ($response->getStatus() > $this->maxStatusCode) {
            throw new ValidationFailedException('Status code ' . $response->getStatus() . ' found.');
        }
    }
}
