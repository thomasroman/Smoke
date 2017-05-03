<?php

namespace whm\Smoke\Rules\Xml\Rss;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a rss feed is valid.
 */
class ValidRule implements Rule
{
    const SCHEMA = 'rss2_0.xsd';

    private function getSchema()
    {
        return __DIR__ . '/' . self::SCHEMA;
    }

    public function validate(ResponseInterface $response)
    {
        if ($response->getContentType() !== 'text/xml') {
            return;
        }

        $body = (string)$response->getBody();
        if (preg_match('/<rss/', $body)) {
            libxml_clear_errors();
            $dom = new \DOMDocument();
            @$dom->loadXML($body);
            $lastError = libxml_get_last_error();
            if ($lastError) {
                throw new ValidationFailedException(
                    'The given xml file is not well formed (last error: ' .
                    str_replace("\n", '', $lastError->message) . ').');
            }
            $valid = @$dom->schemaValidate($this->getSchema());
            if (!$valid) {
                $lastError = libxml_get_last_error();
                $lastErrorMessage = str_replace("\n", '', $lastError->message);
                throw new ValidationFailedException(
                    'The given xml file did not Validate vs. ' .
                    $this->getSchema() . ' (last error: ' .
                    $lastErrorMessage . ').');
            }
        }
    }
}
