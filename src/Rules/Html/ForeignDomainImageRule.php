<?php

namespace whm\Smoke\Rules\Html;

use whm\Html\Document;
use whm\Html\Uri;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rules detects images that are not from the same domain as the request url.
 */
class ForeignDomainImageRule implements Rule
{
    private $depth;

    /**
     * @param int $depth number of url parts that have to be the same
     */
    public function init($depth = 2)
    {
        $this->depth = $depth;
    }

    public function validate(Response $response)
    {
        if (!$response->getContentType() === 'text/html') {
            return;
        }

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

        if (count($foreignImages) > 0) {
            $foreignImagesString = implode(', ', $foreignImages);
            throw new ValidationFailedException('Images from a foreign domain where found (' . $foreignImagesString . ')');
        }
    }
}
