<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Scanner\Scanner;

class CliReporter implements Reporter, OutputAwareReporter
{
    private $output;
    private $results = array();

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function processResult($result)
    {
        $this->results[] = $result;
    }

    public function finish()
    {
        $this->output->writeln("\n\n <comment>Passed tests:</comment> \n");

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

        $this->output->writeln('');
    }
}
