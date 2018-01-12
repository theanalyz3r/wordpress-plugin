<?php

/**
 *
 * PHP version 5 and 7
 *
 * @author Qordoba Team <support@qordoba.com>
 * @copyright 2018 Qordoba Team
 *
 */

namespace Respect\Validation\Rules;

use stdClass;

/**
 * @group  rule
 * @covers Respect\Validation\Rules\Identical
 * @covers Respect\Validation\Exceptions\IdenticalException
 */
class IdenticalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerForIdentical
     */
    public function testInputIdenticalToExpectedValueShouldPass($compareTo, $input)
    {
        $rule = new Identical($compareTo);

        $this->assertTrue($rule->validate($input));
    }

    /**
     * @dataProvider providerForNotIdentical
     */
    public function testInputNotIdenticalToExpectedValueShouldPass($compareTo, $input)
    {
        $rule = new Identical($compareTo);

        $this->assertFalse($rule->validate($input));
    }

    /**
     * @expectedException Respect\Validation\Exceptions\IdenticalException
     * @expectedExceptionMessage "42" must be identical as 42
     */
    public function testShouldThrowTheProperExceptionWhenFailure()
    {
        $rule = new Identical(42);
        $rule->check('42');
    }

    public function providerForIdentical()
    {
        $object = new stdClass();

        return [
            ['foo', 'foo'],
            [[], []],
            [$object, $object],
            [10, 10],
        ];
    }

    public function providerForNotIdentical()
    {
        return [
            [42, '42'],
            ['foo', 'bar'],
            [[1], []],
            [new stdClass(), new stdClass()],
        ];
    }
}
