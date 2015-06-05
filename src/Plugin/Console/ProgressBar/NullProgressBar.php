<?php

namespace whm\Smoke\Pulgin\Console\ProgressBar;

class NullProgressBar
{
    /**
     * @Event("Scanner.Scan.Begin")
     */
    public function start()
    {
    }
}
