<?php

namespace whm\Smoke\Rules\Seo;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks Google Pagespeed.
 */
class PageSpeedRule implements Rule
{
    public function validate(Response $response)
    {
        $url = (string) $response->getUri();
        throw new ValidationFailedException("This function isn't implemented yet");
    }
}
