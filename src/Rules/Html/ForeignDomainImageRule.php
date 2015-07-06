<?php

namespace whm\Smoke\Rules\Html;

use whm\Html\Document;
use whm\Html\Uri;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rules detects images that are not from the same domain as the request url.
 */
class ForeignDomainImageRule extends StandardRule
{
    private $depth;

    protected $contentTypes = array('text/html');

    /**
     * @param int $depth number of url parts that have to be the same
     */
    public function init($depth = 2)
    {
        $this->depth = $depth;
    }

    protected function doValidation(Response $response)
    {
        $document = new Document($response->getBody());
        $images = $document->getImages($response->getUri());

        $foreignImages = array();

        /* @var $currentUri Uri */
        $currentUri = $response->getUri();

        foreach ($images as $image) {
            /* @var $image Uri */
            if ($currentUri->getHost($this->depth) !== $image->getHost($this->depth)) {
                $foreignImages[] = (string) $image;
            }
        }

        $this->assert(count($foreignImages) == 0, 'Images from a foreign domain where found (' . implode(', ', $foreignImages) . ')');
    }
}
