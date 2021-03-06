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
 * Validator for Tunisia subdivision code.
 *
 * ISO 3166-1 alpha-2: TN
 *
 * @link http://www.geonames.org/TN/administrative-division-tunisia.html
 */
class TnSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        '11', // Tunis
        '12', // L'Ariana
        '13', // Ben Arous
        '14', // La Manouba
        '21', // Nabeul
        '22', // Zaghouan
        '23', // Bizerte
        '31', // Béja
        '32', // Jendouba
        '33', // Le Kef
        '34', // Siliana
        '41', // Kairouan
        '42', // Kasserine
        '43', // Sidi Bouzid
        '51', // Sousse
        '52', // Monastir
        '53', // Mahdia
        '61', // Sfax
        '71', // Gafsa
        '72', // Tozeur
        '73', // Kebili
        '81', // Gabès
        '82', // Medenine
        '83', // Tataouine
    ];

    public $compareIdentical = true;
}
