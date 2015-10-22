<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Scanner\Result;

class LiveReporter extends CliReporter
{
    public function init(OutputInterface $_output)
    {
        $this->setOutputInterface($_output);
    }

    public function processResult(Result $result)
    {
        if ($result->isSuccess()) {
            $this->renderSuccess($result);
        } else {
            $this->renderFailure($result);
        }
    }

    public function finish()
    {
    }
}
