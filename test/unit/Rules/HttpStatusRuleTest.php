<?php

namespace unit\Rules;

use whm\Smoke\Rules\Http\Header\HttpStatusRule;

class HttpStatusRuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $response = null;

    public function setUp()
    {
        $this->response = $this->getMockBuilder('\whm\Smoke\Http\Response')->getMock();
    }

    public function testValidateDefaultHandlingIsValid()
    {
        $testStatus = 200;
        $rule = new HttpStatusRule();
        $this->response->method('getStatus')->willReturn($testStatus);

        $exceptionWasThrown = false;
        try {
            $rule->validate($this->response);
        } catch (\Exception $e) {
            $exceptionWasThrown = true;
        }

        $this->assertFalse($exceptionWasThrown);
    }

    public function testValidateDefaultHandlingStatusDoesNotMatch()
    {
        $testStatus = 301;
        $rule = new HttpStatusRule();
        $this->response->method('getStatus')->willReturn($testStatus);

        $exceptionWasThrown = false;
        try {
            $rule->validate($this->response);
        } catch (\Exception $e) {
            $exceptionWasThrown = true;
        }

        $this->assertTrue($exceptionWasThrown);
    }

    public function testValidateGivenStatus()
    {
        $testStatus = 301;
        $rule = new HttpStatusRule();
        $this->response->method('getStatus')->willReturn($testStatus);

        $exceptionWasThrown = false;
        try {
            $rule->init($testStatus);
            $rule->validate($this->response);
        } catch (\Exception $e) {
            $exceptionWasThrown = true;
        }

        $this->assertFalse($exceptionWasThrown);
    }

    public function testValidateGivenStatusDoesNotMatch()
    {
        $testStatus = 301;
        $rule = new HttpStatusRule();
        $this->response->method('getStatus')->willReturn($testStatus);

        $exceptionWasThrown = false;
        try {
            $rule->init(404);
            $rule->validate($this->response);
        } catch (\Exception $e) {
            $exceptionWasThrown = true;
        }

        $this->assertTrue($exceptionWasThrown);
    }
}
