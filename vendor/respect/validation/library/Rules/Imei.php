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

class Imei extends AbstractRule
{
    const IMEI_SIZE = 15;

    /**
     * @see https://en.wikipedia.org/wiki/International_Mobile_Station_Equipment_Identity
     *
     * @param string $input
     *
     * @return bool
     */
    public function validate($input)
    {
        if (!is_scalar($input)) {
            return false;
        }

        $numbers = preg_replace('/\D/', '', $input);
        if (strlen($numbers) != self::IMEI_SIZE) {
            return false;
        }

        $sum = 0;
        for ($position = 0; $position < (self::IMEI_SIZE - 1); ++$position) {
            $number = $numbers[$position] * (($position % 2) + 1);
            $sum += ($number % 10) + intval($number / 10);
        }

        return ((ceil($sum / 10) * 10) - $sum == $numbers[14]);
    }
}
