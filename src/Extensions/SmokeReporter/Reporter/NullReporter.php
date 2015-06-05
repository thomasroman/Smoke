<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Scanner\Scanner;

class NullReporter implements Reporter
{
    public function processResult($result)
    {
    }

    public function finish()
    {
    }
}
