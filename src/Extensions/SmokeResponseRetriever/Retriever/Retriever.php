<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever;

use Ivory\HttpAdapter\HttpAdapterInterface;
use whm\Html\Uri;
use whm\Smoke\Http\Response;

interface Retriever
{
    public function setHttpClient(HttpAdapterInterface $httpClient);

    /**
     * @return Response
     */
    public function next();

    public function getComingFrom(Uri $uri);
}
