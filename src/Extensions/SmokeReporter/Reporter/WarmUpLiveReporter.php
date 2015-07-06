<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Scanner\Result;

class WarmUpLiveReporter implements Reporter, OutputAwareReporter
{
    private $output;
    private $urlCount = 0;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function processResult(Result $result)
    {
        ++$this->urlCount;
        $this->output->writeln('   ' . $result->getUrl());
        $this->output->writeln('');
    }

    public function finish()
    {
        $this->output->writeln('   <comment>Warm up finished. ' . $this->urlCount . ' urls visited.</comment>');
        $this->output->writeln('');
    }
}
