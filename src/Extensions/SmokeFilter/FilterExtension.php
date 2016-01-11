<?php

namespace whm\Smoke\Extensions\SmokeFilter;

use phmLabs\Components\Annovent\Event\Event;
use whm\Smoke\Http\Response;
use whm\Smoke\Yaml\EnvAwareYaml;

/**
 * Class FilterExtension.
 *
 * @example for filter file
 *
 * filters:
 *   _HttpHeaderSuccessStatus:
 *     - http://www.wunderweib.de/tag/
 *     - http://www.amilio.de/old-but-mandatory-file/
 *     - http://www.amilio.de/images/(.*)
 */
class FilterExtension
{
    private $filters = array();

    public function init($filters = array(), $filterFile = '')
    {
        if ($filterFile !== '') {
            if (!file_exists($filterFile)) {
                throw new \RuntimeException('Filter file not found: ' . $filterFile);
            }

            $filterElements = EnvAwareYaml::parse(file_get_contents($filterFile));

            foreach ($filterElements['filters'] as $rule => $uris) {
                foreach ($uris as $uri) {
                    $this->filters[] = array('rule' => $rule, 'uri' => $uri);
                }
            }
        } else {
            $this->filters = $filters;
        }
    }

    /**
     * @Event("Scanner.CheckResponse.isFiltered")
     */
    public function isFiltered(Event $event, $ruleName, Response $response)
    {
        foreach ($this->filters as $filter) {
            if ($ruleName === $filter['rule'] && 0 < preg_match('$' . preg_quote($filter['uri']) . '$', (string) $response->getUri())) {
                $event->setProcessed();

                return true;
            }
        }

        return false;
    }
}
