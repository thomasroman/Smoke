<?php

namespace whm\Smoke\Extensions\SmokeStop\Strategy;

use phmLabs\Components\Annovent\Event\Event;

class TimeStrategy
{
    private $duration;
    private $startTime;

    public function init($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @Event("Scanner.Scan.Begin")
     */
    public function startTimer()
    {
        $this->startTime = time();
    }

    public function isStopped()
    {
        return ($this->startTime + $this->duration) < time();
    }
}
