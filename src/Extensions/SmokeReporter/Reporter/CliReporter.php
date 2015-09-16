<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Scanner\Result;

abstract class CliReporter implements Reporter
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Retriever
     */
    protected $retriever;

    public function setResponseRetriever(Retriever $retriever)
    {
        $this->retriever = $retriever;
    }

    protected function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
    }

    protected function renderFailure(Result $result)
    {
        $this->output->writeln('   <error> ' . $result->getUrl() . ' </error> coming from ' . (string) $this->retriever->getComingFrom($result->getUrl()));
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
