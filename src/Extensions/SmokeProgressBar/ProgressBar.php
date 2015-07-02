<?php

namespace whm\Smoke\Extensions\SmokeProgressBar;

use Symfony\Component\Console\Output\OutputInterface;

class ProgressBar
{
    private $progressBar;

    private $width;
    private $format;

    private $max = 0;
    private $output;

    private $isStarted = false;

    public function init(OutputInterface $_output, $width = 100, $format = 'normal', $max = 0)
    {
        $this->width = $width;
        $this->format = $format;
        $this->max = $max;
        $this->output = $_output;
    }

    public function setMax($max)
    {
        $this->max = $max;
    }

    /**
     * @Event("Scanner.Scan.Begin")
     */
    public function start()
    {
        if (!$this->isStarted) {
            $this->isStarted = true;
            $this->progressBar = new \Symfony\Component\Console\Helper\ProgressBar($this->output, $this->max);
            $this->progressBar->setFormat($this->format);
            $this->progressBar->setBarWidth($this->width);
        }
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
