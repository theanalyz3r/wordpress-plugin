<?php
/**
 *
 * PHP version 5 and 7
 *
 * @author Qordoba Team <support@qordoba.com>
 * @copyright 2018 Qordoba Team
 *
 */

use PHPUnit\Framework\TestCase;

class ExceptionInAssertPostConditionsTest extends TestCase
{
    public $setUp                = false;
    public $assertPreConditions  = false;
    public $assertPostConditions = false;
    public $tearDown             = false;
    public $testSomething        = false;

    protected function setUp()
    {
        $this->setUp = true;
    }

    protected function assertPreConditions()
    {
        $this->assertPreConditions = true;
    }

    public function testSomething()
    {
        $this->testSomething = true;
    }

    protected function assertPostConditions()
    {
        $this->assertPostConditions = true;
        throw new Exception;
    }

    protected function tearDown()
    {
        $this->tearDown = true;
    }
}
