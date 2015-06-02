<?php

/**
 * Created by PhpStorm.
 * User: langn
 * Date: 26.05.15
 * Time: 21:15.
 */
namespace whm\Smoke\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Scanner\Scanner;

class CliReporter implements Reporter
{
    private $output;

    private $results = array();

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function process($result)
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
