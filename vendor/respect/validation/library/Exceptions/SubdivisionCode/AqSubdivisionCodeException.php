<?php

/**
 *
 * PHP version 5 and 7
 *
 * @author Qordoba Team <support@qordoba.com>
 * @copyright 2018 Qordoba Team
 *
 */

namespace Respect\Validation\Exceptions\SubdivisionCode;

use Respect\Validation\Exceptions\SubdivisionCodeException;

/**
 * Exception class for Antarctica subdivision code.
 *
 * ISO 3166-1 alpha-2: AQ
 */
class AqSubdivisionCodeException extends SubdivisionCodeException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be a subdivision code of Antarctica',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be a subdivision code of Antarctica',
        ],
    ];
}
