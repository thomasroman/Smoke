<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Scanner\Result;

class WarmUpLiveReporter implements Reporter
{
    private $output;
    private $urlCount = 0;

    public function init(OutputInterface $_output)
    {
        $this->output = $_output;
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
