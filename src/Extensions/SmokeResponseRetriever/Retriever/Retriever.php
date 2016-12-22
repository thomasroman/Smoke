<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever;

use Ivory\HttpAdapter\HttpAdapterInterface;
use Psr\Http\Message\UriInterface;
use whm\Smoke\Http\Response;
use whm\Smoke\Scanner\SessionContainer;

interface Retriever
{
    public function setHttpClient(HttpAdapterInterface $httpClient);

    /**
     * @return Response
     */
    public function next();

    public function setSessionContainer(SessionContainer $sessionContainer);

    public function getComingFrom(UriInterface $uri);

    public function getOriginUri(UriInterface $uri);
}
