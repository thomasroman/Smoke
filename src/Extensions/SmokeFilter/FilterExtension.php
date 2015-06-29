<?php

namespace whm\Smoke\Extensions\SmokeFilter;

use whm\Html\Uri;
use phmLabs\Components\Annovent\Event\Event;
use PhmLabs\Components\Init\Init;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\SmokeFilter\Filter\ConfigAwareFilter;

/**
 * This extension helps to filter urls that should not be scanned while Smoke is analyzing
 * a website.
 */
class FilterExtension
{
    private $filters = array();

    /**
     * @Event("Scanner.Init")
     */
    public function initFilters(Configuration $configuration)
    {
        if ($configuration->hasSection('filters')) {
            $this->filters = Init::initializeAll($configuration->getSection('filters'));

            foreach ($this->filters as $filter) {
                if ($filter instanceof ConfigAwareFilter) {
                    $filter->setConfiguration($configuration);
                }
            }
        }
    }

    /**
     * @Event("Scanner.ProcessHtml.isFiltered")
     */
    public function isFiltered(Event $event, Uri $uri)
    {
        foreach ($this->filters as $filter) {
            if ($filter->isFiltered($uri)) {
                $event->setProcessed();

                return true;
            }
        }

        return false;
    }
}
