<?php

namespace whm\Smoke\Rules\Http;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule can validate if a http request takes longer than a given max duration.
 * A website that is slower than one second is considered as slow.
 */
class DurationRule implements Rule
{
    private $maxDuration;

    /**
     * @param int $maxDuration The maximum duration a http call is allowed to take (time to first byte)
     */
    public function init($maxDuration = 1000)
    {
        $this->maxDuration = $maxDuration;
    }

    public function validate(Response $response)
    {
        if ($response->getDuration() > $this->maxDuration) {
            throw new ValidationFailedException('The http request took ' . $response->getDuration() . ' milliseconds (limit was '.$this->maxDuration.'ms). ');
        }
    }
}
