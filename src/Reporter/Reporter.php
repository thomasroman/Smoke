<?php

namespace whm\Smoke\Reporter;

interface Reporter
{
    public function finish();

    public function process($result);
}
