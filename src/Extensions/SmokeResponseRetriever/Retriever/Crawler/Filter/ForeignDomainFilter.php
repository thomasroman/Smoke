<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Crawler\Filter;

use Psr\Http\Message\UriInterface;
use whm\Crawler\Filter;
use whm\Html\Uri;

class ForeignDomainFilter implements Filter
{
    public function isFiltered(UriInterface $currentUri, UriInterface $startUri)
    {
        /* @var $currentUri Uri */
        /* @var $startUri Uri */

        $startDomainElements = explode('.', $startUri->getHost());
        $currentDomainElements = explode('.', $currentUri->getHost());

        $startDomainLength = count($startDomainElements);
        $currentDomainLength = count($currentDomainElements);

        if ($currentDomainLength < $startDomainLength) {
            return false;
        }

        return $currentUri->getHost($startDomainLength) !== $startUri->getHost($startDomainLength);
    }
}
