<?php

namespace whm\Smoke\Extensions\SmokeFilter\Filter;

use whm\Html\Uri;

interface Filter
{
    public function isFiltered(Uri $uri);
}
