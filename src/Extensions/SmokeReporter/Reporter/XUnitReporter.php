<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Scanner\Result;

/**
 * Class XUnitReporter.
 */
class XUnitReporter implements Reporter, OutputAwareReporter, ConfigAwareReporter
{
    private $filename = null;

    /**
     * @var Result[]
     */
    private $results = array();

    private $output = null;

    private $startUri;

    public function init($filename)
    {
        $this->filename = $filename;

        if (!is_dir(dirname($this->filename))) {
            mkdir(dirname($this->filename));
        }
    }

    public function setConfig(Configuration $config)
    {
        $this->startUri = $config->getStartUri();
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
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
            $testCase->setAttribute('name', '');
            $testCase->setAttribute('assertions', '1');
            $testCase->setAttribute('time', $result->getDuration());

            if ($result->isFailure()) {
                ++$failures;

                foreach ($result->getMessages() as $type => $message) {
                    $testFailure = $xml->createElement('failure');
                    $testCase->appendChild($testFailure);

                    $testFailure->setAttribute('type', $type);
                    $text = $xml->createTextNode($message);
                    $testFailure->appendChild($text);
                }
            }

            $testSuite->appendChild($testCase);
        }

        // @TODO: differentiate between errors and failures

        $testSuite->setAttribute('name', (string) $this->startUri);
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
