<?php

namespace whm\Smoke\Rules;

use Psr\Http\Message\ResponseInterface;

abstract class StandardRule implements Rule
{
    protected $contentTypes = array();

    public function validate(ResponseInterface $response)
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

        return $this->doValidation($response);
    }

    abstract protected function doValidation(ResponseInterface $response);

    protected function assert($valueToBeTrue, $errorMessage)
    {
        if (!$valueToBeTrue) {
            throw new ValidationFailedException($errorMessage);
        }
    }
}
