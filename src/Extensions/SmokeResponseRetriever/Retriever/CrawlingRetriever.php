<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever;

use Psr\Http\Message\UriInterface;

interface CrawlingRetriever extends Retriever
{
    public function getStartPage();

    public function setStartPage(UriInterface $uri);
}
