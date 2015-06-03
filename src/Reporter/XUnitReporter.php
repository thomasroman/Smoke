<?php

namespace whm\Smoke\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Scanner\Scanner;

class XUnitReporter implements Reporter
{
    private $filename;

    private $results = array();

    public function init($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @Event("Scanner.Scan.Validate")
     */
    public function process($result)
    {
        $this->results[] = $result;
    }

    /**
     * @Event("Scanner.Scan.Finish")
     */
    public function finish()
    {
        echo "writing xunit file to " . $this->filename;
    }
}
