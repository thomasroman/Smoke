<?php

namespace whm\Smoke\Rules\Xml;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule checks if the found XML is valide.
 */
class XmlCheckRule extends StandardRule
{
    public function doValidation(Response $response)
    {
        $domDocument = new \DOMDocument();
        $success = @$domDocument->loadXML((string) $response->getBody());  // true/false

        if (!$success) {
            throw new \RuntimeException('XML called from "' . $response->getUri() . '" is not well-formed...');
        }
    }
}
