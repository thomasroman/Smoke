<?php

namespace whm\Smoke\Extensions\SmokeFilter\Filter;

use Phly\Http\Uri;
use whm\Smoke\Config\Configuration;

class ForeignDomainFilter implements Filter, ConfigAwareFilter
{
    private $startUri;

    public function isFiltered(Uri $uri)
    {
        $tlds = explode('.', $uri->getHost());

        if (count($tlds) < 2) {
            return true;
        }

        $currentTld = $tlds[count($tlds) - 2] . '.' . $tlds[count($tlds) - 1];

        $tlds = explode('.', $this->startUri->getHost());
        $startTld = $tlds[count($tlds) - 2] . '.' . $tlds[count($tlds) - 1];

        return ($currentTld !== $startTld);
    }

    public function setConfiguration(Configuration $config)
    {
        $this->startUri = $config->getStartUri();
    }
}
