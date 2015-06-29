<?php

namespace whm\Smoke\Extensions\SmokeFilter\Filter;

use whm\Html\Uri;
use whm\Smoke\Config\Configuration;

class BlackWhiteListFilter implements Filter, ConfigAwareFilter
{
    private $blacklist = array();
    private $whitelist;

    public function isFiltered(Uri $uri)
    {
        foreach ($this->whitelist as $whitelist) {
            if (preg_match($whitelist, (string) $uri)) {
                foreach ($this->blacklist as $blacklist) {
                    if (preg_match($blacklist, (string) $uri)) {
                        return true;
                    }
                }

                return false;
            }
        }

        return true;
    }

    public function setConfiguration(Configuration $config)
    {
        if ($config->hasSection('blacklist')) {
            $this->blacklist = $config->getSection('blacklist');
        }

        if ($config->hasSection('whitelist')) {
            $this->whitelist = $config->getSection('whitelist');
        }
    }
}
