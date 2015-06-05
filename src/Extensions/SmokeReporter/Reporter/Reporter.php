<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

interface Reporter
{
    public function processResult($result);

    public function finish();
}
