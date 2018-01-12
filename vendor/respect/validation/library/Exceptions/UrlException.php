<?php

/**
 *
 * PHP version 5 and 7
 *
 * @author Qordoba Team <support@qordoba.com>
 * @copyright 2018 Qordoba Team
 *
 */

namespace Respect\Validation\Exceptions;

class UrlException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be an URL',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be an URL',
        ],
    ];
}
