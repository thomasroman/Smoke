<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever;

use Ivory\HttpAdapter\HttpAdapterInterface;
use PhmLabs\Components\Init\Init;
use whm\Smoke\Config\Configuration;

class ResponseRetrieverExtension
{
    private $retriever;

    public function init(Configuration $_configuration)
    {
        if ($_configuration->hasSection('responseRetriever')) {
            $this->retriever = Init::initialize($_configuration->getSection('responseRetriever'));
        } else {
            throw new \RuntimeException("No response retriever set. Please check the config file if a section 'responseRetriever' exists.");
        }
    }

    /**
     * @Event("Scanner.Init")
     */
    public function setRetriever(HttpAdapterInterface $httpClient)
    {
        $this->retriever->setHttpClient($httpClient);
    }

    public function getRetriever()
    {
        return $this->retriever;
    }
}
