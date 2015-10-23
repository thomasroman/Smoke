<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\CrawlingRetriever;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Scanner\Result;

/**
 * Class XUnitReporter.
 */
class XUnitReporter implements Reporter
{
    private $filename = null;

    /**
     * @var Result[]
     */
    private $results = array();

    private $output = null;

    private $config;

    /**
     * @var Retriever
     */
    protected $retriever;

    public function setResponseRetriever(Retriever $retriever)
    {
        $this->retriever = $retriever;
    }

    public function init($filename, Configuration $_configuration, OutputInterface $_output)
    {
        $this->filename = $filename;
        $this->config = $_configuration;
        $this->output = $_output;


        if (!is_dir(dirname($this->filename))) {
            mkdir(dirname($this->filename));
        }
    }

    public function processResult(Result $result)
    {
        $this->results[] = $result;
    }

    public function finish()
    {
        $failures = 0;
        $absoluteTime = 0;

        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $xmlRoot = $xml->createElement('testsuites');
        $xml->appendChild($xmlRoot);

        $testSuite = $xml->createElement('testsuite');

        $xmlRoot->appendChild($testSuite);

        foreach ($this->results as $result) {
            $absoluteTime += $result->getDuration();

            $testCase = $xml->createElement('testcase');

            $testCase->setAttribute('classname', $result->getUrl());
            $testCase->setAttribute('name', $result->getUrl());
            $testCase->setAttribute('assertions', '1');
            $testCase->setAttribute('time', $result->getDuration());

            if ($result->isFailure()) {
                ++$failures;

                foreach ($result->getMessages() as $ruleName => $message) {
                    $testFailure = $xml->createElement('failure');
                    $testFailure->setAttribute('message', $message);
                    $testCase->appendChild($testFailure);

                    $testFailure->setAttribute('type', $ruleName);

                    if ($this->retriever instanceof CrawlingRetriever) {
                        $text = $result->getUrl() . ' coming from ' . (string) $this->retriever->getComingFrom($result->getUrl()) . PHP_EOL;
                        $text .= '    - ' . $message . " [rule: $ruleName]";
                        $systemOut = $xml->createElement('system-out', $text);
                        $testCase->appendChild($systemOut);
                    }
                }
            }

            $testSuite->appendChild($testCase);
        }

        // @TODO: differentiate between errors and failures

        if ($this->retriever instanceof CrawlingRetriever) {
            $startPage = (string) $this->retriever->getStartPage();
        } else {
            $startPage = '';
        }

        $testSuite->setAttribute('name', $startPage);
        $testSuite->setAttribute('tests', count($this->results));
        $testSuite->setAttribute('failures', $failures);
        $testSuite->setAttribute('errors', '0');
        $testSuite->setAttribute('time', $absoluteTime);

        $saveResult = $xml->save($this->filename);

        if ($saveResult === false) {
            $this->output->writeln('<error>An error occured: ' . libxml_get_last_error() . '</error>');
        }
        $this->output->writeln('    <info>Writing XUnit Output to file:</info> ' . $this->filename);
    }
}
