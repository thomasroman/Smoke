<?php

namespace whm\Smoke\Extensions\SmokeProgressBar;

class NullProgressBar
{
    /**
     * @Event("Scanner.Scan.Begin")
     */
    public function start()
    {
    }
}
