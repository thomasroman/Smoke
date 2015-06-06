<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Scanner\Result;

class CliReporter implements Reporter, OutputAwareReporter
{
    private $output;
    private $results = array();

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
        $this->output->writeln("\n\n <comment>Passed tests:</comment> \n");

        foreach ($this->results as $result) {
            if ($result->isSuccess()) {
                $this->output->writeln('   <info> ' . $result->getUrl() . ' </info> all tests passed');
            }
        }

        $this->output->writeln("\n <comment>Failed tests:</comment> \n");

        foreach ($this->results as $result) {
            if ($result->isFailure()) {
                $this->output->writeln('   <error> ' . $result->getUrl() . ' </error> coming from ' . $result->getParent());
                foreach ($result->getMessages() as $ruleName => $message) {
                    $this->output->writeln('    - ' . $message . " [rule: $ruleName]");
                }
                $this->output->writeln('');
            }
        }

        $this->output->writeln('');
    }
}
