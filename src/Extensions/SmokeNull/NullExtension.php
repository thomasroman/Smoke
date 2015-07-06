<?php

namespace whm\Smoke\Extensions\SmokeNull;

use phmLabs\Components\Annovent\Annotation\Event;

class NullExtension
{
    /**
     * @Event("Scanner.Scan.Begin")
     */
    public function nullFunction()
    {
    }
}
