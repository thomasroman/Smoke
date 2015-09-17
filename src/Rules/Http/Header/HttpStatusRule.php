<?php

namespace whm\Smoke\Rules\Http\Header;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This class validates, whether the response has a specific status.
 * Can be used to test, if your homepage has a 200 status or your error page sends a 404.
 */
class HttpStatusRule implements Rule
{
    protected $expectedStatus = 200;

    /**
     * @param int $expectedStatus The expected HTTP-status
     */
    public function init($expectedStatus = 200)
    {
        $this->expectedStatus = $expectedStatus;
    }

    public function validate(Response $response)
    {
        if ($response->getStatus() != $this->expectedStatus)
        {
            throw new ValidationFailedException('Status code ' . $response->getStatus() . ' found, ' . $this->expectedStatus . ' expected.');
        }
    }
}
