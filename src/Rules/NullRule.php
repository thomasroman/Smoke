<?php

namespace whm\Smoke\Rules;

use whm\Smoke\Http\Response;

class NullRule implements Rule
{
    public function validate(Response $response)
    {
        // this rule can be used to override standard rules in config
    }
}
