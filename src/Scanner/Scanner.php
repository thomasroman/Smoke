<?php

namespace whm\Smoke\Scanner;

use Ivory\HttpAdapter\HttpAdapterInterface;
use phmLabs\Components\Annovent\Dispatcher;
use phmLabs\Components\Annovent\Event\Event;
use whm\Html\Uri;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\ValidationFailedException;

class Scanner
{
    const ERROR = 'error';
    const PASSED = 'passed';

    private $configuration;
    private $eventDispatcher;

    private $responseRetriever;

    private $status = 0;

    public function __construct(Configuration $config, HttpAdapterInterface $client, Dispatcher $eventDispatcher, Retriever $responseRetriever)
    {
        $eventDispatcher->simpleNotify('Scanner.Init', array('configuration' => $config, 'httpClient' => $client, 'dispatcher' => $eventDispatcher));

        $this->configuration = $config;
        $this->eventDispatcher = $eventDispatcher;

        $this->responseRetriever = $responseRetriever;
    }

    public function scan()
    {
        $this->eventDispatcher->simpleNotify('Scanner.Scan.Begin');

        while (($response = $this->responseRetriever->next()) && !$this->eventDispatcher->notifyUntil(new Event('Scanner.Scan.isStopped'))) {

            // this is the url filter
            if ($this->eventDispatcher->notifyUntil(new Event('Scanner.ProcessHtml.isFiltered', array('uri' => $response->getUri())))) {
                continue;
            }

            $resultArray = $this->checkResponse($response);

            $result = new Result($response->getUri(),
                $resultArray['type'],
                $response,
                '',
                // $this->pageContainer->getParent($response->getUri()),
                $resultArray['time']);

            if ($result->isFailure()) {
                $result->setMessages($resultArray['messages']);
                $this->status = 1;
            }

            $this->eventDispatcher->simpleNotify('Scanner.Scan.Validate', array('result' => $result));
        }

        $this->eventDispatcher->simpleNotify('Scanner.Scan.Finish');
    }

    public function getStatus()
    {
        return $this->status;
    }

    private function checkResponse(Response $response)
    {
        $messages = [];

        $startTime = microtime(true);
        foreach ($this->configuration->getRules() as $name => $rule) {
            try {
                $rule->validate($response);
            } catch (ValidationFailedException $e) {
                $messages[$name] = $e->getMessage();
            }
        }
        $endTime = microtime(true);

        // calculate time in seconds
        $time = round(($endTime - $startTime) * 1000, 5);

        if ($messages) {
            $resultArray = ['messages' => $messages, 'time' => $time, 'type' => self::ERROR];
        } else {
            $resultArray = ['messages' => [], 'time' => $time, 'type' => self::PASSED];
        }

        return $resultArray;
    }
}
