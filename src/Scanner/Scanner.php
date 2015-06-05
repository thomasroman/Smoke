<?php

namespace whm\Smoke\Scanner;

use Phly\Http\Uri;
use phmLabs\Components\Annovent\Dispatcher;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Http\Document;
use whm\Smoke\Http\HttpClient;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\ValidationFailedException;

class Scanner
{
    const ERROR = 'error';
    const PASSED = 'passed';

    private $configuration;
    private $eventDispatcher;

    /**
     * @var HttpClient
     */
    private $client;

    private $status = 0;

    public function __construct(Configuration $config, HttpClient $client, Dispatcher $eventDispatcher)
    {
        $this->pageContainer = new PageContainer($config->getContainerSize());
        $this->pageContainer->push($config->getStartUri(), $config->getStartUri());
        $this->client = $client;
        $this->configuration = $config;
        $this->eventDispatcher = $eventDispatcher;
    }

    private function processHtmlContent($htmlContent, Uri $currentUri)
    {
        $htmlDocument = new Document($htmlContent);
        $referencedUris = $htmlDocument->getReferencedUris($currentUri);

        foreach ($referencedUris as $uri) {
            if ($this->configuration->isUriAllowed($uri)) {
                $this->pageContainer->push($uri, $currentUri);
            }
        }
    }

    public function scan()
    {
        $this->eventDispatcher->simpleNotify('Scanner.Scan.Begin');

        do {
            $urls = $this->pageContainer->pop($this->configuration->getParallelRequestCount());
            $responses = $this->client->request($urls);

            foreach ($responses as $response) {
                $currentUri = new Uri((string) $response->getUri());

                // only extract urls if the content type is text/html
                if ('text/html' === $response->getContentType()) {
                    $this->processHtmlContent($response->getBody(), $currentUri);
                }

                $violation = $this->checkResponse($response);
                $violation['parent'] = $this->pageContainer->getParent($currentUri);
                $violation['contentType'] = $response->getContentType();
                $violation['url'] = $response->getUri();

                if ($violation['type'] === self::ERROR) {
                    $this->status = 1;
                }

                $this->eventDispatcher->simpleNotify('Scanner.Scan.Validate', array('result' => $violation));
            }
        } while (count($urls) > 0);

        $this->eventDispatcher->simpleNotify('Scanner.Scan.Finish');
    }

    public function getStatus()
    {
        return $this->status;
    }

    private function checkResponse(Response $response)
    {
        $messages = [];

        foreach ($this->configuration->getRules() as $name => $rule) {
            $startTime = microtime(true);
            try {
                $rule->validate($response);
            } catch (ValidationFailedException $e) {
                $messages[$name] = $e->getMessage();
            }
            $endTime = microtime(true);
        }

        // calculate time in seconds
        $time = round(($endTime - $startTime) * 1000, 5);

        if ($messages) {
            $violation = ['messages' => $messages, 'time' => $time, 'type' => self::ERROR];
        } else {
            $violation = ['messages' => [], 'time' => $time, 'type' => self::PASSED];
        }

        return $violation;
    }
}
