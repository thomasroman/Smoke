<?php

namespace whm\Smoke\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Scanner\Scanner;

class CliReporter
{
    private $output;

    private $results = array();

    /**
     * @Event("ScannerCommand.Output.Register")
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
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
