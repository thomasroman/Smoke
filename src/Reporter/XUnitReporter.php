<?php

namespace whm\Smoke\Reporter;

class XUnitReporter
{
    private $filename;

    private $results = array();

    public function init($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @Event("Scanner.Scan.Validate")
     */
    public function process($result)
    {
        $this->results[] = $result;
    }

    /**
     * @Event("Scanner.Scan.Finish")
     */
    public function finish()
    {
        $failures = 0;

        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $xmlRoot = $xml->createElement('testsuites');
        $xml->appendChild($xmlRoot);

        $testSuite = $xml->createElement('testsuite');
        $xmlRoot->appendChild($testSuite);

        foreach ($this->results as $result) {
            $testCase = $xml->createElement('testcase');

            $testCase->setAttribute('classname', $result['type']);
            $testCase->setAttribute('file', $result['url']);
            $testCase->setAttribute('name', '');
            $testCase->setAttribute('class', '');
            //$testCase->setAttribute('feature', $result['messages']);
            $testCase->setAttribute('assertions', '1');
            $testCase->setAttribute('time', '0');

            switch ($result['type']) {
                case 'passed':

                    break;

                case 'error':
                    $failures++;
                    $testFailure = $xml->createElement('failure');
                    $testCase->appendChild($testFailure);

                    $testFailure->setAttribute('type', implode('; ', array_keys($result['messages'])));
                    $text = $xml->createTextNode(implode('; ', $result['messages']));
                    $testFailure->appendChild($text);

                    break;

                default:
                    throw new \Exception($result['type'] . 'result type not known');
            }

            $testSuite->appendChild($testCase);
        }

        // @TODO: calculate amount of assertions (global and for every test)
        // @TODO: differentiate between errors and failures
        // @TODO: calculate testing time (global and for every test)

        $testSuite->setAttribute('name', reset($this->results)['parent']);
        $testSuite->setAttribute('tests', count($this->results));
        $testSuite->setAttribute('assertions', count($this->results));
        $testSuite->setAttribute('failures', $failures);
        $testSuite->setAttribute('errors', '0');
        $testSuite->setAttribute('time', '0.5');

        $xml->save($this->filename);

        echo 'Writing XUnit Output to file: ' . $this->filename;
    }
}
