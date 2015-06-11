<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use whm\Smoke\Config\Configuration;

interface ConfigAwareReporter
{
    /**
     * This function can be used to hand over the configuration.
     */
    public function setConfig(Configuration $config);
}
