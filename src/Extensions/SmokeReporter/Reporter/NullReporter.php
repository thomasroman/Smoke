<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

class NullReporter implements Reporter
{
    public function processResult($result)
    {
    }

    public function finish()
    {
    }
}
