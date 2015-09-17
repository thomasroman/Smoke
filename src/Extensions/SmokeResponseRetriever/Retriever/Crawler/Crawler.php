<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Crawler;

use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\MultiHttpAdapterException;
use PhmLabs\Components\Init\Init;
use Psr\Http\Message\UriInterface;
use whm\Crawler\Crawler as whmCrawler;
use whm\Crawler\PageContainer\PatternAwareContainer;
use whm\Html\Uri;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Http\Response;

class Crawler implements Retriever
{
    private $startPage;
    private $httpClient;
    private $parallelRequests;

    private $started = false;
    private $filters;

    /**
     * @var \whm\Crawler\Crawler
     */
    private $crawler;

    public function init(array $filters, $startPage = null, $parallelRequests = 5)
    {
        $this->filters = Init::initializeAll($filters);
        if (!is_null($startPage)) {
            $this->startPage = new Uri($startPage);
        }

        $this->parallelRequests = $parallelRequests;
    }

    public function setStartPage(Uri $startPage)
    {
        $this->startPage = $startPage;
    }

    public function setHttpClient(HttpAdapterInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return Response
     */
    public function next()
    {
        if (!$this->started) {
            $this->started = true;

            if (is_null($this->startPage)) {
                throw new \RuntimeException('The crawler you are using needs a start page to work, but it is not defined. ');
            }

            $this->crawler = new whmCrawler($this->httpClient, new PatternAwareContainer(), $this->startPage, $this->parallelRequests);

            foreach ($this->filters as $filter) {
                $this->crawler->addFilter($filter);
            }
        }
        try {
            $next = $this->crawler->next();
        } catch (MultiHttpAdapterException $e) {
            throw new \RuntimeException();
        }
        return $next;
    }

    public function getComingFrom(UriInterface $uri)
    {
        return $this->crawler->getComingFrom($uri);
    }
}
