<?php

namespace whm\Smoke\Extensions\SmokeFilter\Filter;

use Phly\Http\Uri;

class NullFilter implements Filter
{
    public function isFiltered(Uri $uri)
    {
        return false;
    }
}
