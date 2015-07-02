<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Crawler\Filter;

use Psr\Http\Message\UriInterface;
use whm\Crawler\Filter;

class ForeignDomainFilter implements Filter
{
    public function isFiltered(UriInterface $currentUri, UriInterface $startUri)
    {
        return $currentUri->getHost(2) !== $startUri->getHost(2);
    }
}
