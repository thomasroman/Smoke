<?php

namespace whm\Smoke\Rules;

class Result
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    private $status;
    private $value;
    private $message;
    private $attributes = array();

    /**
     * Result constructor.
     *
     * @param $status
     * @param $value
     * @param $message
     */
    public function __construct($status, $message = '', $value = null)
    {
        $this->status = $status;
        $this->value = $value;
        $this->message = $message;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }
}
