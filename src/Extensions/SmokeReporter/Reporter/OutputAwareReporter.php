<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

interface OutputAwareReporter
{
    /**
     * This function can be used to hand over an output interface. This
     * can be useful if you want to write a cli reporter.
     */
    public function setOutput(OutputInterface $output);
}
