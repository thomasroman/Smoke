<?php

namespace whm\Smoke\Rules;

use whm\Smoke\Http\Response;

class CheckResult
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';
    const STATUS_NONE = 'none';

    private $status;
    private $value;
    private $message;
    private $attributes = array();
    private $ruleName;

    /**
     * @var Response
     */
    private $response;

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

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getRuleName()
    {
        return $this->ruleName;
    }

    /**
     * @param mixed $ruleName
     */
    public function setRuleName($ruleName)
    {
        $this->ruleName = $ruleName;
    }
}