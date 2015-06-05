<?php

namespace whm\Smoke\Extensions\SmokeProgressBar;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;

class ProgressBar
{
    private $progressBar;
    private $config;

    private $width;
    private $format;

    /**
     * @Event("ScannerCommand.Output.Register")
     */
    public function registerOutput(OutputInterface $output)
    {
        $this->progressBar = new \Symfony\Component\Console\Helper\ProgressBar($output, $this->config->getContainerSize());

        $this->progressBar->setFormat($this->format);
        $this->progressBar->setBarWidth($this->width);
    }

    /**
     * @Event("ScannerCommand.Config.Register")
     */
    public function registerConfig(Configuration $config)
    {
        $this->config = $config;
    }

    public function init($width = 100, $format = 'normal')
    {
        $this->width = $width;
        $this->format = $format;
    }

    /**
     * @Event("Scanner.Scan.Begin")
     */
    public function start()
    {
        $this->progressBar->start();
    }

    /**
     * @Event("Scanner.Scan.Finish")
     */
    public function finish()
    {
        $this->progressBar->finish();
    }

    /**
     * @Event("Scanner.Scan.Validate")
     */
    public function advance()
    {
        $this->progressBar->advance();
    }
}
