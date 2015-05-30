<?php

namespace whm\Smoke\Rules\Http\Header\Cache;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * Checks if the max-age cache header ist not 0.
 */
class MaxAgeRule implements Rule
{
    public function validate(Response $response)
    {
        if ($response->hasHeader('Cache-Control') && false !== strpos($response->getHeader('Cache-Control')[0], 'max-age=0')) {
            throw new ValidationFailedException('max-age=0 was found');
        }
    }
}
