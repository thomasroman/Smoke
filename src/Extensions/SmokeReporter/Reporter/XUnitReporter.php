<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Scanner\Result;

class XUnitReporter implements Reporter, OutputAwareReporter
{
    private $filename = null;

    /**
     * @var Result[]
     */
    private $results = array();

    private $output = null;

    public function init($filename)
    {
        $this->filename = $filename;
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

            $testCase->setAttribute('classname', '');
            $testCase->setAttribute('file', $result->getUrl());
            $testCase->setAttribute('name', '');
            $testCase->setAttribute('class', '');
            //$testCase->setAttribute('feature', $result['messages']);
            $testCase->setAttribute('assertions', '1');
            $testCase->setAttribute('time', $result->getDuration());

            if ($result->isFailure()) {
                ++$failures;
                $testFailure = $xml->createElement('failure');
                $testCase->appendChild($testFailure);

                $testFailure->setAttribute('type', implode('; ', array_keys($result->getMessages())));
                $text = $xml->createTextNode(implode('; ', $result->getMessages()));
                $testFailure->appendChild($text);
            }

            $testSuite->appendChild($testCase);
        }

        // @TODO: calculate amount of assertions (global and for every test)
        // @TODO: differentiate between errors and failures

        $testSuite->setAttribute('name', '');
        $testSuite->setAttribute('tests', count($this->results));
        $testSuite->setAttribute('assertions', count($this->results));
        $testSuite->setAttribute('failures', $failures);
        $testSuite->setAttribute('errors', '0');
        $testSuite->setAttribute('time', $absoluteTime);

        $xml->save($this->filename);

        $this->output->writeln('<info>Writing XUnit Output to file:</info> ' . $this->filename);
    }
}
