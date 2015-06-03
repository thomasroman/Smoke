<?php

namespace whm\Smoke\Reporter;

use whm\Smoke\Event\Listener;

interface Reporter
{
    public function finish();

    public function process($result);
}