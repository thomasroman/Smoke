<?php
namespace whm\Smoke\Rules\Http\Header;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This class validates, wether the response has a specific status
 */
class HttpStatusRule implements Rule
{
    protected $expectedStatus = 200;

    /**
     * @param int $expectedStatus The maximum size of a html file in kilobytes.
     */
    public function init($expectedStatus = 200)
    {
        $this->expectedStatus = $expectedStatus;
    }

    public function validate(Response $response)
    {
        if ($response->getStatus() != $this->expectedStatus) {
            throw new ValidationFailedException('Status code ' . $response->getStatus() . ' found, ' . $this->expectedStatus . ' expected.');
        }
    }
}
