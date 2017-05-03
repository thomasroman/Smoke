<?php

/*
 * This rule will find external ressources on a https transfered page that are insecure (http).
 *
 * @author Nils Langner <nils.langner@phmlabs.com>
 * @inspiredBy Christian Haller
 */

namespace whm\Smoke\Rules\Html;

use Psr\Http\Message\ResponseInterface;
use whm\Html\Document;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a https document uses http (insecure) ressources.
 */
class InsecureContentRule implements Rule
{
    public function validate(ResponseInterface $response)
    {
        $uri = $response->getUri();

        if ('https' !== $uri->getScheme()) {
            return;
        }

        $htmlDocument = new Document((string)$response->getBody());

        $resources = $htmlDocument->getDependencies($uri, false);

        foreach ($resources as $resource) {
            if ($resource->getScheme() && 'https' !== $resource->getScheme()) {
                throw new ValidationFailedException('At least one dependency was found on a secure url, that was transfered insecure (' . (string)$resource . ')');
            }
        }
    }
}
