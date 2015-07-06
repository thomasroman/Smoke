<?php

namespace whm\Smoke\Rules;

use whm\Smoke\Http\Response;

interface Rule
{
    public function validate(Response $response);
}
