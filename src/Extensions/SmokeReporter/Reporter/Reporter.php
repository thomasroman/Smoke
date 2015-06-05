<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

interface Reporter
{
    /**
     * This *optional* function can be used to hand over an output interface. This
     * can be useful if you want to write a cli reporter.
     */
    // public function setOutput(OutputInterface $output);

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
