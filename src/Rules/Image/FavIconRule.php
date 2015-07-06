<?php

namespace whm\Smoke\Rules\Image;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if the favicon that is used is the framework default.
 */
class FavIconRule implements Rule
{
    private $favicons = array('231567a8cc45c2cf966c4e8d99a5b7fd' => 'symfony2');

    public function validate(Response $response)
    {
        if (strpos($response->getContentType(), 'image') === false) {
            return;
        }

        if (strpos((string) $response->getUri(), 'favicon.ico') === false) {
            return;
        }

        $hash = md5($response->getBody());

        if (array_key_exists($hash, $this->favicons)) {
            throw new ValidationFailedException('Seems like you use the standard favicon of your framework (' . $this->favicons[$hash] . ').');
        }
    }
}
