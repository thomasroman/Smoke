<?php

namespace whm\Smoke\Extensions\SmokeReporter;

use PhmLabs\Components\Init\Init;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Scanner\Scanner;

class ReporterExtension
{
    private $reporters;

    /**
     * @Event("ScannerCommand.Output.Register")
     */
    public function setOutput(OutputInterface $output)
    {
        foreach ($this->reporters as $reporter) {
            if (method_exists($reporter, "setOutput")) {
                $reporter->setOutput($output);
            }
        }
    }

    /**
     * @Event("ScannerCommand.Config.Register")
     */
    public function setReporter(Configuration $config)
    {
        if ($config->hasSection("reporter")) {
            $this->reporters = Init::initializeAll($config->getSection("reporter"));
        }

    }

    /**
     * @Event("Scanner.Scan.Validate")
     */
    public function process($result)
    {
        foreach ($this->reporters as $reporter) {
            $reporter->processResult($result);
        }
    }

    /**
     * @Event("Scanner.Scan.Finish")
     */
    public function finish()
    {
        foreach ($this->reporters as $reporter) {
            $reporter->finish();
        }
    }
}
