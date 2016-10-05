<?php

namespace whm\Smoke\Rules\Xml;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if the found XML is valide.
 */
class XmlCheckRule extends StandardRule
{
    protected $contentTypes = array('text/xml', 'application/xml');

    public function doValidation(Response $response)
    {
        $domDocument = new \DOMDocument();
        $success = @$domDocument->loadXML((string) $response->getBody());  // true/false

        $lastError = libxml_get_last_error();
        if (!$success || $lastError) {
            throw new ValidationFailedException('The xml file ' . $response->getUri() . ' is not well formed (last error: ' .
                str_replace("\n", '', $lastError->message) . ').');
        }
    }
}
