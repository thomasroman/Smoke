<?php

namespace whm\Smoke\Extensions\SmokeFilter;

use phmLabs\Components\Annovent\Event\Event;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Http\Response;

class FilterExtension
{
    private $filters = array();

    public function init(Configuration $_configuration, $filters = array())
    {
        $this->filters = $filters;
    }

    /**
     * @Event("Scanner.CheckResponse.isFiltered")
     */
    public function isFiltered(Event $event, $ruleName, Response $response)
    {
        foreach ($this->filters as $filter) {
            if ($ruleName === $filter['rule'] && 0 < preg_match($filter['uri'], (string) $response->getUri())) {
                $event->setProcessed();

                return true;
            }
        }

        return false;
    }
}
