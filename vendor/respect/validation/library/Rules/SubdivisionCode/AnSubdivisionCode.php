<?php

/**
 *
 * PHP version 5 and 7
 *
 * @author Qordoba Team <support@qordoba.com>
 * @copyright 2018 Qordoba Team
 *
 */

namespace Respect\Validation\Rules\SubdivisionCode;

use Respect\Validation\Rules\AbstractSearcher;

/**
 * Validator for Netherlands Antilles subdivision code.
 *
 * ISO 3166-1 alpha-2: AN
 *
 * @link http://www.geonames.org/AN/administrative-division-netherlands-antilles.html
 */
class AnSubdivisionCode extends AbstractSearcher
{
    public $haystack = [null, ''];

    public $compareIdentical = true;
}
