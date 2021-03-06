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

use ReflectionObject;

class AbstractWrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Respect\Validation\Exceptions\ComponentException
     * @expectedExceptionMessage There is no defined validatable
     */
    public function testShouldThrowsAnExceptionWhenWrappedValidatableIsNotDefined()
    {
        $wrapper = $this->getMockForAbstractClass('Respect\Validation\Rules\AbstractWrapper');
        $wrapper->getValidatable();
    }

    private function bindValidatable($wrapper, $validatable)
    {
        $reflectionObject = new ReflectionObject($wrapper);
        $reflectionProperty = $reflectionObject->getProperty('validatable');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($wrapper, $validatable);
    }

    public function testShouldReturnDefinedValidatable()
    {
        $validatable = $this->getMock('Respect\Validation\Validatable');

        $wrapper = $this->getMockForAbstractClass('Respect\Validation\Rules\AbstractWrapper');
        $this->bindValidatable($wrapper, $validatable);

        $this->assertSame($validatable, $wrapper->getValidatable());
    }

    public function testShouldUseWrappedToValidate()
    {
        $input = 'Whatever';

        $validatable = $this->getMock('Respect\Validation\Validatable');
        $validatable
            ->expects($this->once())
            ->method('validate')
            ->with($input)
            ->will($this->returnValue(true));

        $wrapper = $this->getMockForAbstractClass('Respect\Validation\Rules\AbstractWrapper');
        $this->bindValidatable($wrapper, $validatable);

        $this->assertTrue($wrapper->validate($input));
    }

    public function testShouldUseWrappedToAssert()
    {
        $input = 'Whatever';

        $validatable = $this->getMock('Respect\Validation\Validatable');
        $validatable
            ->expects($this->once())
            ->method('assert')
            ->with($input)
            ->will($this->returnValue(true));

        $wrapper = $this->getMockForAbstractClass('Respect\Validation\Rules\AbstractWrapper');
        $this->bindValidatable($wrapper, $validatable);

        $this->assertTrue($wrapper->assert($input));
    }

    public function testShouldUseWrappedToCheck()
    {
        $input = 'Whatever';

        $validatable = $this->getMock('Respect\Validation\Validatable');
        $validatable
            ->expects($this->once())
            ->method('check')
            ->with($input)
            ->will($this->returnValue(true));

        $wrapper = $this->getMockForAbstractClass('Respect\Validation\Rules\AbstractWrapper');
        $this->bindValidatable($wrapper, $validatable);

        $this->assertTrue($wrapper->check($input));
    }

    public function testShouldPassNameOnToWrapped()
    {
        $name = 'Whatever';

        $validatable = $this->getMock('Respect\Validation\Validatable');
        $validatable
            ->expects($this->once())
            ->method('setName')
            ->with($name)
            ->will($this->returnValue($validatable));

        $wrapper = $this->getMockForAbstractClass('Respect\Validation\Rules\AbstractWrapper');
        $this->bindValidatable($wrapper, $validatable);

        $this->assertSame($wrapper, $wrapper->setName($name));
    }
}
