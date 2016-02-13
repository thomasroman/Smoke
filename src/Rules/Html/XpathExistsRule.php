<?php

namespace whm\Smoke\Rules\Html;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule checks if xpath is found in a html document.
 */
class XpathExistsRule extends StandardRule
{
    protected $contentTypes = ['text/html'];

    private $xPaths;

    public function init(array $xPaths)
    {
        $this->xPaths = $xPaths;
    }

    public function doValidation(Response $response)
    {
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML((string) $response->getBody());

        $domXPath = new \DOMXPath($domDocument);

        foreach ($this->xPaths as $xpath) {
            $this->assert($domXPath->query($xpath)->length > 0, 'The xpath ' . $xpath . ' was not found.');
        }
    }
}
