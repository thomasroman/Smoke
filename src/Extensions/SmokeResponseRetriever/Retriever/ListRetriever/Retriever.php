<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\ListRetriever;

use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\Message\Request;
use Psr\Http\Message\UriInterface;
use whm\Html\Uri;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever as SmokeRetriever;

class Retriever implements SmokeRetriever
{
    private $urls;
    private $httpClient;
    private $urlStack;

    public function init(array $urls)
    {
        foreach ($urls as $key => $urlList) {

            foreach ($urlList as $url) {
                $this->urls[$url] = ['url' => $url, 'system' => $key];
            }
        }

        $this->urlStack = $this->urls;
    }

    public function next()
    {
        if (empty($this->urlStack)) {
            return false;
        }

        $url = array_pop($this->urlStack);

        $request = new Request(new Uri($url['url']), 'GET', 'php://memory', ['Accept-Encoding' => 'gzip']);

        try {
            $responses = $this->httpClient->sendRequests(array($request));
        } catch (\Ivory\HttpAdapter\MultiHttpAdapterException $e) {
            return $this->next();
        }


        return $responses[0];
    }

    public function getComingFrom(UriInterface $uri)
    {
        return $uri;
    }

    public function getSystem(UriInterface $uri)
    {
        return $this->urls[(string)$uri]['system'];
    }

    public function getSystems()
    {
        $systems = [];
        foreach ($this->urls as $key => $url) {
            $systems[] = $url['system'];
        }

        return $systems;
    }

    public function setHttpClient(HttpAdapterInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}
