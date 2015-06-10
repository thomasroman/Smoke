<?php

namespace whm\Smoke\Extensions\SmokeStop;

use phmLabs\Components\Annovent\Dispatcher;
use phmLabs\Components\Annovent\Event\Event;
use PhmLabs\Components\Init\Init;
use whm\Smoke\Config\Configuration;

class StopExtension
{
    private $stopStrategies = array();
    private $dispatcher;

    /**
     * @Event("Scanner.Init")
     */
    public function setReporter(Configuration $configuration, Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        if ($configuration->hasSection('stop')) {
            $strategies = $configuration->getSection('stop');

            foreach ($strategies as $name => $strategy) {
                $this->stopStrategies[$name] = Init::initialize($strategy);
                $this->dispatcher->connectListener($this->stopStrategies[$name]);
            }
        }
    }

    /**
     * @Event("Scanner.Scan.isStopped")
     */
    public function isStopped(Event $event)
    {
        foreach ($this->stopStrategies as $strategy) {
            if ($strategy->isStopped()) {
                $event->setProcessed();

                return true;
            }
        }

        return false;
    }

    public function getStrategy($name)
    {
        return $this->stopStrategies[$name];
    }
}
