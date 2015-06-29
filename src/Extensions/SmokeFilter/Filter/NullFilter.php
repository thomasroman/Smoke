<?php

namespace whm\Smoke\Extensions\SmokeFilter\Filter;

use whm\Html\Uri;

class NullFilter implements Filter
{
    public function isFiltered(Uri $uri)
    {
        return false;
    }
}
