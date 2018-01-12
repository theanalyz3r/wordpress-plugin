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
 * Validator for Nigeria subdivision code.
 *
 * ISO 3166-1 alpha-2: NG
 *
 * @link http://www.geonames.org/NG/administrative-division-nigeria.html
 */
class NgSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        'AB', // Abia
        'AD', // Adamawa
        'AK', // Akwa Ibom
        'AN', // Anambra
        'BA', // Bauchi
        'BE', // Benue
        'BO', // Borno
        'BY', // Bayelsa
        'CR', // Cross River
        'DE', // Delta
        'EB', // Ebonyi
        'ED', // Edo
        'EK', // Ekiti
        'EN', // Enugu
        'FC', // Federal Capital Territory
        'GO', // Gombe
        'IM', // Imo
        'JI', // Jigawa
        'KD', // Kaduna
        'KE', // Kebbi
        'KN', // Kano
        'KO', // Kogi
        'KT', // Katsina
        'KW', // Kwara
        'LA', // Lagos
        'NA', // Nassarawa
        'NI', // Niger
        'OG', // Ogun
        'ON', // Ondo
        'OS', // Osun
        'OY', // Oyo
        'PL', // Plateau
        'RI', // Rivers
        'SO', // Sokoto
        'TA', // Taraba
        'YO', // Yobe
        'ZA', // Zamfara
    ];

    public $compareIdentical = true;
}
