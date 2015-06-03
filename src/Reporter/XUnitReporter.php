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
        echo "writing XUnit file to " . $this->filename . PHP_EOL;

        /**
         * <testsuite name="nosetests" tests="1" errors="1" failures="0" skip="0">
                <testcase classname="path_to_test_suite.TestSomething"
                name="test_it" time="0">
                <error type="exceptions.TypeError" message="oops, wrong type">
                Traceback (most recent call last):
                ...
                TypeError: oops, wrong type
                </error>
                </testcase>
            </testsuite>
         */

        /**
         *
           foreach ($this->results as $result) {
                if ($result['type'] === Scanner::PASSED) {
                    $this->output->writeln('   <info> ' . $result['url'] . ' </info> all tests passed');
                }
            }

            $this->output->writeln("\n <comment>Failed tests:</comment> \n");

            foreach ($this->results as $result) {
                if ($result['type'] === Scanner::ERROR) {
                    $this->output->writeln('   <error> ' . $result['url'] . ' </error> coming from ' . $result['parent']);
                    foreach ($result['messages'] as $ruleName => $message) {
                        $this->output->writeln('    - ' . $message . " [rule: $ruleName]");
                    }
                    $this->output->writeln('');
                }
            }
         */

        /* create a dom document with encoding utf8 */
        $domTree = new \DOMDocument('1.0', 'UTF-8');

        /* create the root element of the xml tree */
        $xmlRoot = $domtree->createElement('testsuite');
        $xmlRoot = $domtree->appendChild($xmlRoot);

        foreach($this->results as $result)
        {
            $testCase = $domTree->createElement('testcase');
            $testType = $domTree->createAttribute('classname');
            $testType->value = $result['type'];

            $testCase->appendChild($testType);
            $xmlRoot->appendChild($testCase);
        }

        /* get the xml printed */
        echo $domtree->saveXML();

    }
}
