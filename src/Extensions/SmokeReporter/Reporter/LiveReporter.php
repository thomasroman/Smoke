<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Scanner\Result;

class LiveReporter implements Reporter, OutputAwareReporter
{
    private $output;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function processResult(Result $result)
    {
        if ($result->isSuccess()) {
            $this->output->writeln('   <info> ' . $result->getUrl() . '</info> all tests passed. ');
        } else {
            $this->output->writeln('   <error> ' . $result->getUrl() . ' </error> coming from ' . $result->getParent());
            foreach ($result->getMessages() as $ruleName => $message) {
                $this->output->writeln('    - ' . $message . " [rule: $ruleName]");
            }
        }
        $this->output->writeln('');
    }

    public function finish()
    {
    }
}
