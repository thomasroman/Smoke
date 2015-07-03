<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Crawler\Filter;

use Psr\Http\Message\UriInterface;
use whm\Crawler\Filter;
use whm\Html\Uri;

class ForeignDomainFilter implements Filter
{
    public function isFiltered(UriInterface $currentUri, UriInterface $startUri)
    {
        /* @var $currentUri */
        /* @var $startUri Uri */

        return $currentUri->getHost(2) !== $startUri->getHost(2);
    }
}
