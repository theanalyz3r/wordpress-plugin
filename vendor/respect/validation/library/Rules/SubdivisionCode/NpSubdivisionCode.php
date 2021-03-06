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
 * Validator for Nepal subdivision code.
 *
 * ISO 3166-1 alpha-2: NP
 *
 * @link http://www.geonames.org/NP/administrative-division-nepal.html
 */
class NpSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        '1', // Madhyamanchal
        '2', // Madhya Pashchimanchal
        '3', // Pashchimanchal
        '4', // Purwanchal
        '5', // Sudur Pashchimanchal
        'BA', // Bagmati
        'BH', // Bheri
        'DH', // Dhawalagiri
        'GA', // Gandaki
        'JA', // Janakpur
        'KA', // Karnali
        'KO', // Kosi
        'LU', // Lumbini
        'MA', // Mahakali
        'ME', // Mechi
        'NA', // Narayani
        'RA', // Rapti
        'SA', // Sagarmatha
        'SE', // Seti
    ];

    public $compareIdentical = true;
}
