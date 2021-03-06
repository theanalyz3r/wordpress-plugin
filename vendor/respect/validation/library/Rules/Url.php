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

class Url extends FilterVar
{
    public function __construct()
    {
        parent::__construct(FILTER_VALIDATE_URL);
    }
}
