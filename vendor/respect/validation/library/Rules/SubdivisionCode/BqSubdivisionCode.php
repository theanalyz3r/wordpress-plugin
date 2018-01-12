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
 * Validator for Bonaire subdivision code.
 *
 * ISO 3166-1 alpha-2: BQ
 *
 * @link http://www.geonames.org/BQ/administrative-division-bonaire.html
 */
class BqSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        'BO', // Bonaire
        'SA', // Saba
        'SE', // Sint Eustatius
    ];

    public $compareIdentical = true;
}
