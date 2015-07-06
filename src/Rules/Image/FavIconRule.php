<?php

namespace whm\Smoke\Rules\Image;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule checks if the favicon that is used is the framework default.
 */
class FavIconRule extends StandardRule
{
    protected $contentTypes = array('image');

    private $favicons = array('231567a8cc45c2cf966c4e8d99a5b7fd' => 'symfony2');

    protected function doValidation(Response $response)
    {
        if (strpos((string) $response->getUri(), 'favicon.ico') === false) {
            return;
        }

        $imageHash = md5($response->getBody());

        $this->assert(!array_key_exists($imageHash, $this->favicons), 'Seems like you use the standard favicon of your framework (' . $this->favicons[$imageHash] . ').');
    }
}
