<?php
/**
 *
 * PHP version 5 and 7
 *
 * @author Qordoba Team <support@qordoba.com>
 * @copyright 2018 Qordoba Team
 *
 */

class BeforeClassWithOnlyDataProviderTest extends \PHPUnit\Framework\TestCase
{
    public static $setUpBeforeClassWasCalled;
    public static $beforeClassWasCalled;

    public static function resetProperties()
    {
        self::$setUpBeforeClassWasCalled = false;
        self::$beforeClassWasCalled      = false;
    }

    public static function setUpBeforeClass()
    {
        self::$setUpBeforeClassWasCalled = true;
    }

    /**
     * @beforeClass
     */
    public static function someAnnotatedSetupMethod()
    {
        self::$beforeClassWasCalled = true;
    }

    public function dummyProvider()
    {
        return [[1]];
    }

    /**
     * @dataProvider dummyProvider
     * delete annotation to fail test case
     */
    public function testDummy()
    {
        $this->assertFalse(false);
    }
}
