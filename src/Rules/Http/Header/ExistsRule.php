<?php

namespace whm\Smoke\Rules\Http\Header;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if gzip compressions is activated.
 */
class ExistsRule extends StandardRule
{
    private $checkedHeaders;

    public function init(array $checkedHeaders)
    {
        $this->checkedHeaders = $checkedHeaders;
    }

    public function doValidation(Response $response)
    {
        foreach ($this->checkedHeaders as $headerConfig) {
            if (!$response->hasHeader($headerConfig['key'])) {
                throw new ValidationFailedException('Header not found (' . $headerConfig['key'] . ')');
            }

            $currentValue = $response->getHeader($headerConfig['key'])[0];

            if ($currentValue != $headerConfig['value']) {
                throw new ValidationFailedException('Header "' . $headerConfig['key'] . '" does not equal "' . $headerConfig['value'] . '". Current value is "' . $currentValue . '"');
            }
        }
    }
}
