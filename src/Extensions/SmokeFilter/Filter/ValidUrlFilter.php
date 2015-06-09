<?php

namespace whm\Smoke\Extensions\SmokeFilter\Filter;

use Phly\Http\Uri;

class ValidUrlFilter implements Filter
{
    public function isFiltered(Uri $uri)
    {
        return (!filter_var((string) $uri, FILTER_VALIDATE_URL));
    }
}
