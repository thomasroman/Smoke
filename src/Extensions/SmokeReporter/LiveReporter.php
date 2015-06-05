<?php

namespace whm\Smoke\Extensions\SmokeReporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Scanner\Scanner;

class LiveReporter
{
    private $output;
    private $totalCount;
    private $currentCount = 0;

    /**
     * @Event("ScannerCommand.Output.Register")
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @Event("ScannerCommand.Config.Register")
     */
    public function setCponfig(Configuration $config)
    {
        $this->totalCount = $config->getContainerSize();
    }

    /**
     * @Event("Scanner.Scan.Validate")
     */
    public function process($result)
    {
        $this->currentCount++;

        $position = $this->currentCount . "/" . $this->totalCount . " ";

        if ($result['type'] === Scanner::PASSED) {
            $this->output->writeln('   <info> ' . $result['url'] . '</info> all tests passed. ');
        } else {
            $this->output->writeln('   <error> ' . $result['url'] . ' </error> coming from ' . $result['parent']);
            foreach ($result['messages'] as $ruleName => $message) {
                $this->output->writeln('    - ' . $message . " [rule: $ruleName]");
            }

        }
        $this->output->writeln('');
    }
}
