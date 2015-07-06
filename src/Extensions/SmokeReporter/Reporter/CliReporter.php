<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Scanner\Result;

abstract class CliReporter implements Reporter
{
    /**
     * @var OutputInterface
     */
    protected $output;

    public function init(OutputInterface $_output)
    {
        $this->output = $_output;
    }

    protected function renderFailure(Result $result)
    {
        $this->output->writeln('   <error> ' . $result->getUrl() . ' </error> coming from ' . $result->getParent());
        foreach ($result->getMessages() as $ruleName => $message) {
            $this->output->writeln('    - ' . $message . " [rule: $ruleName]");
        }
        $this->output->writeln('');
    }

    protected function renderSuccess(Result $result)
    {
        $this->output->writeln('   <info> ' . $result->getUrl() . ' </info> all tests passed');
    }
}
