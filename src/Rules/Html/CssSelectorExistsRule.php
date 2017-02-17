<?php

namespace whm\Smoke\Rules\Html;

use Symfony\Component\CssSelector\CssSelectorConverter;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if xpath is found in a html document.
 */
class CssSelectorExistsRule extends StandardRule
{
    protected $contentTypes = ['text/html'];

    private $cssSelectors;

    public function init(array $cssSelectors)
    {
        $this->cssSelectors = $cssSelectors;
    }

    public function doValidation(Response $response)
    {
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML((string)$response->getBody());

        $domXPath = new \DOMXPath($domDocument);

        $error = false;
        $snotFoundSelectors = array();

        foreach ($this->cssSelectors as $selector) {
            $converter = new CssSelectorConverter();

            try {
                $selectorAsXPath = $converter->toXPath($selector['pattern']);
            } catch (\Exception $e) {
                throw new ValidationFailedException('Invalid css selector (' . $selector['pattern'] . ').');
            }

            $count = $domXPath->query($selectorAsXPath)->length;

            if ($count === 0) {
                $error = true;
                $snotFoundSelectors[] = $selector['pattern'];
            }
        }

        if ($error === true) {
            $allNotFoundSelectors = implode('", "', $snotFoundSelectors);

            throw new ValidationFailedException('CSS Selector "' . $allNotFoundSelectors . '" not found in DOM.');
        }
    }
}
