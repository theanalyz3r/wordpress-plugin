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

use Respect\Validation\Exceptions\ComponentException;

class Between extends AllOf
{
    public $minValue;
    public $maxValue;

    public function __construct($min = null, $max = null, $inclusive = true)
    {
        $this->minValue = $min;
        $this->maxValue = $max;
        if (!is_null($min) && !is_null($max) && $min > $max) {
            throw new ComponentException(sprintf('%s cannot be less than  %s for validation', $min, $max));
        }

        if (!is_null($min)) {
            $this->addRule(new Min($min, $inclusive));
        }

        if (!is_null($max)) {
            $this->addRule(new Max($max, $inclusive));
        }
    }
}
