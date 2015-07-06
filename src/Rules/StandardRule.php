<?php

namespace whm\Smoke\Rules;

use whm\Smoke\Http\Response;

abstract class StandardRule implements Rule
{
    protected $contentTypes = array();

    public function validate(Response $response)
    {
        if (count($this->contentTypes) > 0) {
            $valid = false;
            foreach ($this->contentTypes as $validContentType) {
                if (strpos($response->getContentType(), $validContentType) !== false) {
                    $valid = true;
                    break;
                }
            }
            if (!$valid) {
                return;
            }
        }
        $this->doValidation($response);
    }

    abstract protected function doValidation(Response $response);

    protected function assert($valueToBeTrue, $errorMessage)
    {
        if (!$valueToBeTrue) {
            throw new ValidationFailedException($errorMessage);
        }
    }
}
