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

use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validatable;

abstract class RuleTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * It is to provide constructor arguments and.
     *
     * @return array
     */
    abstract public function providerForValidInput();

    /**
     * @return array
     */
    abstract public function providerForInvalidInput();

    /**
     * @param bool             $expectedResult
     * @param string[optional] $mockClassName
     *
     * @return \Respect\Validation\Validatable
     */
    public function getRuleMock($expectedResult, $mockClassName = '')
    {
        $ruleMocked = $this->getMockBuilder('Respect\Validation\Validatable')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'assert', 'check', 'getName', 'reportError', 'setName', 'setTemplate', 'validate',
                ]
            )
            ->setMockClassName($mockClassName)
            ->getMock();

        $ruleMocked
            ->expects($this->any())
            ->method('validate')
            ->willReturn($expectedResult)
        ;

        if ($expectedResult) {
            $ruleMocked
                ->expects($this->any())
                ->method('check')
                ->willReturn($expectedResult)
            ;
            $ruleMocked
                ->expects($this->any())
                ->method('assert')
                ->willReturn($expectedResult)
            ;
        } else {
            $ruleMocked
                ->expects($this->any())
                ->method('check')
                ->willThrowException(new ValidationException('Exception for '.$mockClassName.':check() method'))
            ;
            $ruleMocked
                ->expects($this->any())
                ->method('assert')
                ->willThrowException(new ValidationException('Exception for '.$mockClassName.':assert() method'))
            ;
        }

        return $ruleMocked;
    }

    /**
     * @dataProvider providerForValidInput
     *
     * @param Validatable $validator
     * @param mixed       $input
     */
    public function testShouldValidateValidInput(Validatable $validator, $input)
    {
        $this->assertTrue($validator->validate($input));
    }

    /**
     * @dataProvider providerForInvalidInput
     *
     * @param Validatable $validator
     * @param mixed       $input
     */
    public function testShouldValidateInvalidInput(Validatable $validator, $input)
    {
        $this->assertFalse($validator->validate($input));
    }
}
