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

class CliReporter
{
    private $output;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function render($results)
    {
        $this->output->writeln("\n\n <comment>Passed tests:</comment> \n");

        foreach ($results as $url => $result) {
            if ($result['type'] === Scanner::PASSED) {
                $this->output->writeln('   <info> ' . $url . ' </info> all tests passed');
            }
        }

        $this->output->writeln("\n <comment>Failed tests:</comment> \n");

        foreach ($results as $url => $result) {
            if ($result['type'] === Scanner::ERROR) {
                $this->output->writeln('   <error> ' . $url . ' </error> coming from ' . $result['parent']);
                foreach ($result['messages'] as $ruleName => $message) {
                    $this->output->writeln('    - ' . $message . " [rule: $ruleName]");
                }
                $this->output->writeln('');
            }
        }

        $this->output->writeln('');
    }
}
