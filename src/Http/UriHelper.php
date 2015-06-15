<?php

namespace whm\Smoke\Http;

use Psr\Http\Message\UriInterface;

class UriHelper
{
    public static function isSameDomain(UriInterface $uri1, UriInterface $uri2, $depth = 2)
    {
        $domainOneElements = explode('.', $uri1->getHost());
        $domainTwoElements = explode('.', $uri2->getHost());

        $domainOne = '';
        $domainTwo = '';

        for ($i = count($domainOneElements) - 1; $i > count($domainOneElements) - $depth - 1; $i--) {
            $domainOne .= $domainOneElements[$i] . '.';
        }

        for ($i = count($domainTwoElements) - 1; $i > count($domainTwoElements) - $depth - 1; $i--) {
            $domainTwo .= $domainTwoElements[$i] . '.';
        }

        return ($domainOne === $domainTwo);
    }
}
