<?php

namespace whm\Smoke\Rules\Seo;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if robots.txt has no entry "Disallow:/".
 */
class RobotsDisallowAllRule implements Rule
{
    public function validate(Response $response)
    {
        $url = $response->getUri()->getScheme() . '://' . $response->getUri()->getHost();

        if (substr_count($url, '/') === 2) {
            $filename = $robotsUrl = $url . '/robots.txt';
        } elseif (substr_count($url, '/') === 3) {
            $filename = $robotsUrl = $url . 'robots.txt';
        } else {
            return;
        }

        $headers = @get_headers($filename);

        if (strpos($headers[0], '200') !== false) {
            $content = file_get_contents($filename);
            $normalizedContent = strtolower(str_replace(' ', '', $content));

            if (strpos($normalizedContent, 'disallow:/' . PHP_EOL) !== false) {
                throw new ValidationFailedException('The robots.txt contains disallow all (Disallow: /)');
            }

            if (strpos($normalizedContent, 'disallow:/') === strlen($normalizedContent) - 10) {
                throw new ValidationFailedException('The robots.txt contains disallow all (Disallow: /)');
            }
        }
    }
}
