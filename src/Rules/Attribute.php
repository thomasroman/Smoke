<?php

namespace whm\Smoke\Rules;

class Attribute
{
    private $key;
    private $value;
    private $isStorable;

    public function __construct($key, $value, $isStorable = false)
    {
        $this->key = $key;
        $this->value = $value;
        $this->isStorable = $isStorable;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isIsStorable()
    {
        return $this->isStorable;
    }
}
