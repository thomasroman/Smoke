<?php

namespace whm\Smoke\Rules\Html;

use whm\Html\Document;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

abstract class CountRule implements Rule
{
    protected $maxCount;

    protected $contentType;

    protected $errorMessage;

    /**
     * @param int $maxCount The maximum number of css files that are allowed in one html document.
     */
    public function init($maxCount)
    {
        $this->maxCount = $maxCount;
    }

    abstract protected function getFilesToCount(Document $document, Response $response);

    public function validate(Response $response)
    {
        if (!$response->getContentType() === 'text/html') {
            return;
        }

        $document = new Document($response->getBody());
        $files = $this->getFilesToCount($document, $response);

        if (count($files) > $this->maxCount) {
            throw new ValidationFailedException(sprintf($this->errorMessage, count($files)));
        }
    }
}
