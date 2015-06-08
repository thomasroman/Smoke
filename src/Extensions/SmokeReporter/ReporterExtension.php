<?php

namespace whm\Smoke\Extensions\SmokeReporter;

use PhmLabs\Components\Init\Init;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\SmokeReporter\Reporter\OutputAwareReporter;
use whm\Smoke\Scanner\Result;

class ReporterExtension
{
    private $reporters = array();
    private $output;

    /**
     * @Event("ScannerCommand.Output.Register")
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @Event("Scanner.Init")
     */
    public function setReporter(Configuration $configuration)
    {
        if ($configuration->hasSection('reporter')) {
            $this->reporters = Init::initializeAll($configuration->getSection('reporter'));
        }

        foreach ($this->reporters as $reporter) {
            if ($reporter instanceof OutputAwareReporter) {
                $reporter->setOutput($this->output);
            }
        }
    }

    /**
     * @Event("Scanner.Scan.Validate")
     */
    public function process(Result $result)
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
