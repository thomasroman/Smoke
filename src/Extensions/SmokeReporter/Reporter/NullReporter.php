<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use whm\Smoke\Scanner\Result;

class NullReporter implements Reporter
{
    public function processResult(Result $result)
    {
    }

    public function finish()
    {
    }
}
