<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Koalamon\Client\Reporter\Event;
use Koalamon\Client\Reporter\Reporter as KoalaReporter;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\Leankoala\LeankoalaExtension;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Rules\CheckResult;
use whm\Smoke\Scanner\Result;

/**
 * Class XUnitReporter.
 */
class LeankoalaReporter implements Reporter
{
    /**
     * @var Result[]
     */
    private $results = [];

    private $config;
    private $system;
    private $collect;
    private $identifier;
    private $systemUseRetriever;
    private $tool = 'smoke';
    private $groupBy;
    private $server;
    private $addComingFrom;

    /**
     * @var KoalaReporter
     */
    private $reporter;

    /**
     * @var Retriever
     */
    private $retriever;

    private $output;

    /**
     * @var LeankoalaExtension
     */
    private $leankoalaExtension;

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    public function init($apiKey, Configuration $_configuration, OutputInterface $_output, $server = 'https://webhook.koalamon.com', $system = '', $identifier = '', $tool = '', $collect = true, $systemUseRetriever = false, $groupBy = false, $addComingFrom = true)
    {
        $httpClient = new \GuzzleHttp\Client();
        $this->reporter = new KoalaReporter('', $apiKey, $httpClient, $server);

        $this->config = $_configuration;
        $this->systemUseRetriever = $systemUseRetriever;

        $this->system = $system;
        $this->collect = $collect;
        $this->identifier = $identifier;
        $this->groupBy = $groupBy;

        $this->addComingFrom = $addComingFrom;

        if ($tool) {
            $this->tool = $tool;
        }

        $this->leankoalaExtension = $_configuration->getExtension('Leankoala');

        $this->server = $server;
        $this->output = $_output;
    }

    public function setResponseRetriever(Retriever $retriever)
    {
        $this->retriever = $retriever;
    }

    public function processResults($results)
    {
        $this->results[] = $results;
    }

    public function finish()
    {
        if ($this->collect) {
            $this->sendCollectedResults();
        } else {
            $this->sendSingleResults();
        }
    }

    private function getComponent($ruleName)
    {
        $ruleArray = explode('_', $ruleName);
        $component = array_pop($ruleArray);

        return $component;
    }

    private function sendCollectedResults()
    {
        $checks = [];

        foreach ($this->results as $results) {
            foreach ($results as $result) {
                /* @var CheckResult $result */
                $tool = 'Smoke' . $result->getRuleName();
                $checks[$tool][] = $result;
            }
        }

        foreach ($checks as $toolName => $results) {
            if (count($results) === 0) {
                continue;
            }

            $message = 'The smoke test for #system_name# failed (Rule: ' . $toolName . ').<ul>';
            $status = Event::STATUS_SUCCESS;
            $failureCount = 0;
            $identifier = $toolName . '_' . $this->system;

            foreach ($results as $result) {
                /** @var CheckResult $result */
                if ($result->getStatus() === CheckResult::STATUS_FAILURE) {
                    $comingFrom = '';
                    if ($this->addComingFrom && $this->retriever->getComingFrom($result->getResponse()->getUri())) {
                        $comingFrom = ', coming from: ' . $this->retriever->getComingFrom($result->getResponse()->getUri());
                    }
                    $message .= '<li>' . $result->getMessage() . ' (url: ' . (string)$result->getResponse()->getUri() . $comingFrom . ')</li>';
                    ++$failureCount;
                }
            }
            if ($failureCount > 0) {
                $status = Event::STATUS_FAILURE;
                $message .= '</ul>';
            } else {
                $message = 'All checks for system "#system_name#" succeeded [SmokeBasic:' . $toolName . '].';
            }

            $this->send($identifier, $this->system, $message, $status, $failureCount, $this->tool, $this->system);
        }
    }

    private function sendSingleResults()
    {
        foreach ($this->results as $results) {
            foreach ($results as $result) {
                /* @var CheckResult $result */

                $identifier = '_' . $this->getIdentifier($result);
                $tool = $this->getPrefix($result->getRuleName());

                $component = $this->getComponent($result->getRuleName());
                $system = $this->leankoalaExtension->getSystem($component);

                $this->send(
                    $identifier,
                    $system,
                    $result->getMessage() . ' (url: ' . (string)$result->getResponse()->getUri() . ')',
                    $result->getStatus(),
                    $result->getValue(),
                    $tool,
                    $component
                );
            }
        }
    }

    private function getIdentifier(CheckResult $result)
    {
        return $this->tool . '_' . $result->getRuleName();
    }

    private function getPrefix($string)
    {
        return substr($string, 0, strpos($string, '_'));
    }

    private function send($identifier, $system, $message, $status, $value, $tool, $component)
    {
        if ($status != CheckResult::STATUS_NONE) {
            $event = new Event($identifier, $system, $status, $tool, $message, $value, '', $component);
            $this->reporter->sendEvent($event);
        }
    }
}
