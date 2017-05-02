<?php

namespace whm\Smoke\Rules\Seo;

use Ivory\HttpAdapter\HttpAdapterInterface;
use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Http\ClientAware;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if robots.txt has no entry "Disallow:/".
 */
class RobotsDisallowAllRule implements Rule
{
    /**
     * @var HttpAdapterInterface
     */
    private $client;

    public function validate(ResponseInterface $response)
    {
        $url = $response->getUri()->getScheme() . '://' . $response->getUri()->getHost();

        if (substr_count($url, '/') === 2) {
            $filename = $robotsUrl = $url . '/robots.txt';
        } elseif (substr_count($url, '/') === 3) {
            $filename = $robotsUrl = $url . 'robots.txt';
        } else {
            return;
        }

        try {
            $content = file_get_contents($filename);
        } catch (\Exception $e) {
            return;
        }

        $normalizedContent = $this->normalizeContent($content);

        if (strpos($normalizedContent, 'user-agent:* disallow:/' . PHP_EOL) !== false) {
            throw new ValidationFailedException('The robots.txt contains disallow all (Disallow: /)');
        }

        if (strpos($normalizedContent, 'user-agent:* disallow:/') === strlen($normalizedContent) - 23) {
            throw new ValidationFailedException('The robots.txt contains disallow all (Disallow: /)');
        }
    }

    private function normalizeContent($content)
    {
        $normalizedContent = strtolower($content);
        $normalizedContent = str_replace(' ', '', $normalizedContent);

        $normalizedContent = trim(preg_replace('/\s+/', ' ', $normalizedContent));

        return $normalizedContent;
    }
}
