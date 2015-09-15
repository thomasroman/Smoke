<?php

namespace whm\Smoke\Extensions\SmokeRunLevel;

use phmLabs\Components\Annovent\Event\Event;
use whm\Smoke\Config\Configuration;

class RunLevelExtension
{
    private $runLevels = array();

    private $currentRunLevel;

    public function init(Configuration $_configuration, $runLevel)
    {
        $configArray = $_configuration->getConfigArray();
        $rulesArray = $configArray['rules'];
        $this->currentRunLevel = $runLevel;

        foreach ($rulesArray as $key => $ruleElement) {
            if (array_key_exists('runLevel', $ruleElement)) {
                $this->runLevels[$key] = (int)($ruleElement['runLevel']);
            } else {
                $this->runLevels[$key] = 0;
            }
        }
    }

    public function setRunLevel($runLevel)
    {
        echo "RunLevel: " . $runLevel;
        $this->currentRunLevel = (int)$runLevel;
    }

    /**
     * @Event("Scanner.CheckResponse.isFiltered")
     */
    public function isStopped(Event $event, $ruleName)
    {
        if ($this->runLevels[$ruleName] > $this->currentRunLevel) {
            $event->setProcessed();
            return true;
        }

        return false;
    }
}
