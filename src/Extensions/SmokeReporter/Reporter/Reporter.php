<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

interface Reporter
{
    /**
     * This function will be called after an url has been validated.
     *
     * @param $result the result of the validation
     */
    public function processResult($result);

    /**
     * This function is called after the last url has been validated.
     */
    public function finish();
}
