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

/**
 * @group  rule
 * @covers Respect\Validation\Rules\Pesel
 */
class PeselTest extends RuleTestCase
{
    public function providerForValidInput()
    {
        $rule = new Pesel();

        return [
            [$rule, 0x4EADCD168], // 0x4EADCD168 === 21120209256
            [$rule, 49040501580],
            [$rule, '49040501580'],
            [$rule, '39012110375'],
            [$rule, '50083014540'],
            [$rule, '69090515504'],
            [$rule, '21120209256'],
            [$rule, '01320613891']
        ];
    }

    public function providerForInvalidInput()
    {
        $rule = new Pesel();

        return [
            [$rule, '1'],
            [$rule, '22'],
            [$rule, 'PESEL'],
            [$rule, '0x4EADCD168'],
            [$rule, 'PESEL123456'],
            [$rule, '690905155.4'],
            [$rule, '21120209251'],
            [$rule, '21120209250'],
            [$rule, '01320613890']
        ];
    }
}
