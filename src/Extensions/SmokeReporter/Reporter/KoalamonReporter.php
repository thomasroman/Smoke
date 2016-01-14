<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Koalamon\Client\Reporter\Event;
use Koalamon\Client\Reporter\Reporter as KoalaReporter;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Scanner\Result;

/**
 * Class XUnitReporter.
 */
class KoalamonReporter implements Reporter
{
    /**
     * @var Result[]
     */
    private $results;

    private $config;
    private $system;
    private $collect;
    private $identifier;
    private $tool = 'smoke';

    /**
     * @var KoalaReporter
     */
    private $reporter;

    /*
     * @var Retriever
     */
    private $retriever;

    private $output;

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    public function init($apiKey, Configuration $_configuration, OutputInterface $_output, $system = '', $identifier = '', $tool = '', $collect = true)
    {
        $httpClient = new \GuzzleHttp\Client();
        $this->reporter = new KoalaReporter('', $apiKey, $httpClient);

        $this->config = $_configuration;

        $this->system = $system;
        $this->collect = $collect;
        $this->identifier = $identifier;

        if($tool) {
            $this->tool = $tool;
        }


        $this->output = $_output;
    }

    public function setResponseRetriever(Retriever $retriever)
    {
        $this->retriever = $retriever;
    }

    /**
     * @param Rule [];
     *
     * @return array
     */
    private function getRuleKeys()
    {
        $keys = array();
        foreach ($this->config->getRules() as $key => $rule) {
            $keys[] = $key;
        }

        return $keys;
    }

    public function processResult(Result $result)
    {
        $this->results[] = $result;
    }

    public function finish()
    {
        $this->output->writeln("Sending results to www.koalamon.com ... \n");

        if ($this->collect) {
            $this->sendCollected();
        } else {
            $this->sendSingle();
        }
    }

    private function sendSingle()
    {
        $rules = $this->getRuleKeys();
        foreach ($this->results as $result) {
            $failedTests = array();
            if ($result->isFailure()) {
                foreach ($result->getMessages() as $ruleLKey => $message) {
                    $identifier = 'smoke_' . $ruleLKey . '_' . $result->getUrl();

                    if ($this->system === '') {
                        $system = str_replace('http://', '', $result->getUrl());
                    } else {
                        $system = $this->system;
                    }
                    $this->send($identifier, $system, 'smoke', $message, self::STATUS_FAILURE, (string) $result->getUrl());
                    $failedTests[] = $ruleLKey;
                }
            }
            foreach ($rules as $rule) {
                if (!in_array($rule, $failedTests, true)) {
                    $identifier = 'smoke_' . $rule . '_' . $result->getUrl();

                    if ($this->system === '') {
                        $system = str_replace('http://', '', $result->getUrl());
                    } else {
                        $system = $this->system;
                    }
                    $this->send($identifier, $system, 'smoke_' . $rule, self::STATUS_SUCCESS, (string) $result->getUrl());
                }
            }
        }
    }

    private function sendCollected()
    {
        $failureMessages = array();
        $counter = array();

        foreach ($this->getRuleKeys() as $rule) {
            $failureMessages[$rule] = '';
            $counter[$rule] = 0;
        }

        foreach ($this->results as $result) {
            if ($result->isFailure()) {
                foreach ($result->getMessages() as $ruleLKey => $message) {
                    if ($failureMessages[$ruleLKey] === '') {
                        $failureMessages[$ruleLKey] = '    The smoke test for ' . $this->system . ' failed (Rule: ' . $ruleLKey . ').<ul>';
                    }
                    ++$counter[$ruleLKey];
                    $failureMessages[$ruleLKey] .= '<li>' . $message . '(url: ' . $result->getUrl() . ', coming from: ' . $this->retriever->getComingFrom($result->getUrl()) . ')</li>';
                }
            }
        }

        foreach ($failureMessages as $key => $failureMessage) {
            if ($failureMessage !== '') {
                $this->send($this->identifier . '_' . $key, $this->system, $failureMessage . '</ul>', self::STATUS_FAILURE, '', $counter[$key]);
            } else {
                $this->send($this->identifier . '_' . $key, $this->system, '', self::STATUS_SUCCESS, '', 0);
            }
        }
    }

    public function send($identifier, $system, $message, $status, $url = '', $value = 0)
    {
        $event = new Event($identifier, $system, $status, $this->tool, $message, $value);
        $this->reporter->sendEvent($event);
    }
}
