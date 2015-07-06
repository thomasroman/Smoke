<?php

namespace whm\Smoke\Rules\Html;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule will analyze any html document and checks if a given string is contained.
 */
class StringExistsRule extends StandardRule
{
    private $string;

    protected $contentTypes = array('text/html');

    /**
     * @param int $string The string that the document must contain
     */
    public function init($string)
    {
        $this->string = $string;
    }

    protected function doValidation(Response $response)
    {
        $this->assert(strpos($response->getBody(), $this->string) !== false,
            'The given string (' . $this->string . ') was not found in this document.');
    }
}
