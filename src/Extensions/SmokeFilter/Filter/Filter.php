<?php

namespace whm\Smoke\Extensions\SmokeFilter\Filter;

use Phly\Http\Uri;

interface Filter
{
    public function isFiltered(Uri $uri);
}
