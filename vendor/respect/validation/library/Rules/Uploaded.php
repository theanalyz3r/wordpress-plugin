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

class Uploaded extends AbstractRule
{
    public function validate($input)
    {
        if ($input instanceof \SplFileInfo) {
            $input = $input->getPathname();
        }

        return (is_string($input) && is_uploaded_file($input));
    }
}
