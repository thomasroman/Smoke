<?php

namespace whm\Smoke\Rules\Html;

use phm\HttpWebdriverClient\Http\Response\ContentTypeAwareResponse;
use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a given html document has a closing html tag </html>.
 */
class ClosingHtmlTagRule implements Rule
{
    public function validate(ResponseInterface $response)
    {
        if ($response instanceof ContentTypeAwareResponse) {
            if (($response->getStatusCode() < 300 || $response->getStatusCode() >= 400) && $response->getContentType() === 'text/html') {
                if (stripos($response->getBody(), '</html>') === false) {
                    throw new ValidationFailedException('Closing html tag is missing (document lengh: ' . strlen($response->getBody()) . ').');
                }
            }
        }
    }
}
