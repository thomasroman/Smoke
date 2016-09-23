<?php

namespace whm\Smoke\Rules\Xml\Sitemap;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a sitemap.xml file is valid.
 */
class ValidRule implements Rule
{
    const SCHEMA = 'sitemap0_9.xsd';

    private function getSchema()
    {
        return __DIR__ . '/' . self::SCHEMA;
    }

    public function validate(Response $response)
    {
        if ($response->getContentType() !== 'text/xml') {
            return;
        }

        $body = $response->getBody();
        if (preg_match('/<sitemap/', $body)) {
            libxml_clear_errors();
            $dom = new \DOMDocument();
            @$dom->loadXML($body);
            $lastError = libxml_get_last_error();
            if ($lastError) {
                throw new ValidationFailedException(
                    'The given sitemap.xml file is not well formed (last error: ' .
                    str_replace("\n", '', $lastError->message) . ').');
            }
            $valid = @$dom->schemaValidate($this->getSchema());
            if (!$valid) {
                $lastError = libxml_get_last_error();
                $lastErrorMessage = str_replace("\n", '', $lastError->message);
                throw new ValidationFailedException(
                    'The given sitemap.xml file did not validate vs. ' .
                    $this->getSchema() . ' (last error: ' .
                    $lastErrorMessage . ').');
            }
        }
    }
}
