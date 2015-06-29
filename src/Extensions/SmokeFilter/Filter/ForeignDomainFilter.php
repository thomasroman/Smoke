<?php

namespace whm\Smoke\Extensions\SmokeFilter\Filter;

use whm\Html\Uri;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Http\UriHelper;

class ForeignDomainFilter implements Filter, ConfigAwareFilter
{
    private $startUri;

    public function isFiltered(Uri $uri)
    {
        return !UriHelper::isSameDomain($uri, $this->startUri, 2);
    }

    public function setConfiguration(Configuration $config)
    {
        $this->startUri = $config->getStartUri();
    }
}
