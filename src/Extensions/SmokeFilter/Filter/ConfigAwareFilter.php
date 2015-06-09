<?php

namespace whm\Smoke\Extensions\SmokeFilter\Filter;

use whm\Smoke\Config\Configuration;

interface ConfigAwareFilter
{
    public function setConfiguration(Configuration $config);
}
