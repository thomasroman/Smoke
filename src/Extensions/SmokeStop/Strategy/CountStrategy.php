<?php

namespace whm\Smoke\Extensions\SmokeStop\Strategy;

use phmLabs\Components\Annovent\Event\Event;

class CountStrategy
{
    private $count = 0;
    private $maxCount;

    public function init($maxCount = 20)
    {
        $this->maxCount = $maxCount;
    }

    /**
     * @Event("Scanner.Scan.Validate")
     */
    public function afterValidation()
    {
        ++$this->count;
    }

    public function isStopped()
    {
        return $this->count >= $this->maxCount;
    }
}
