<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Scanner\Scanner;

class LiveReporter implements Reporter
{
    private $output;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function processResult($result)
    {
        if ($result['type'] === Scanner::PASSED) {
            $this->output->writeln('   <info> ' . $result['url'] . '</info> all tests passed. ');
        } else {
            $this->output->writeln('   <error> ' . $result['url'] . ' </error> coming from ' . $result['parent']);
            foreach ($result['messages'] as $ruleName => $message) {
                $this->output->writeln('    - ' . $message . " [rule: $ruleName]");
            }
        }
        $this->output->writeln('');
    }

    public function finish()
    {
    }
}
