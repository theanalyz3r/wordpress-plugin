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

class Charset extends AbstractRule
{
    public $charset = null;

    public function __construct($charset)
    {
        $available = mb_list_encodings();
        $charset = is_array($charset) ? $charset : [$charset];
        $charset = array_filter($charset, function ($c) use ($available) {
            return in_array($c, $available, true);
        });

        if (!$charset) {
            throw new ComponentException(
                'Invalid charset'
            );
        }
        $this->charset = $charset;
    }

    public function validate($input)
    {
        $detectedEncoding = mb_detect_encoding($input, $this->charset, true);

        return in_array($detectedEncoding, $this->charset, true);
    }
}
