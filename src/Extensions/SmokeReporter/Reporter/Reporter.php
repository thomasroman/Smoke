<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use whm\Smoke\Scanner\Result;

interface Reporter
{
    /**
     * This function will be called after an url has been validated.
     *
     * @param $result the result of the validation
     */
    public function processResult(Result $result);

    /**
     * This function is called after the last url has been validated.
     */
    public function finish();
}
