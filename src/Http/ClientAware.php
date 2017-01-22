<?php

namespace whm\Smoke\Http;

use Ivory\HttpAdapter\HttpAdapterInterface;

interface ClientAware
{
    public function setClient(HttpAdapterInterface $client);
}
