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
 * Validator for India subdivision code.
 *
 * ISO 3166-1 alpha-2: IN
 *
 * @link http://www.geonames.org/IN/administrative-division-india.html
 */
class InSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        'AN', // Andaman and Nicobar Islands
        'AP', // Andhra Pradesh
        'AR', // Arunachal Pradesh
        'AS', // Assam
        'BR', // Bihar
        'CH', // Chandigarh
        'CT', // Chhattisgarh
        'DD', // Daman and Diu
        'DL', // Delhi
        'DN', // Dadra and Nagar Haveli
        'GA', // Goa
        'GJ', // Gujarat
        'HP', // Himachal Pradesh
        'HR', // Haryana
        'JH', // Jharkhand
        'JK', // Jammu and Kashmir
        'KA', // Karnataka
        'KL', // Kerala
        'LD', // Lakshadweep
        'MH', // Maharashtra
        'ML', // Meghalaya
        'MN', // Manipur
        'MP', // Madhya Pradesh
        'MZ', // Mizoram
        'NL', // Nagaland
        'OR', // Orissa
        'PB', // Punjab
        'PY', // Pondicherry
        'RJ', // Rajasthan
        'SK', // Sikkim
        'TN', // Tamil Nadu
        'TR', // Tripura
        'UL', // Uttaranchal
        'UP', // Uttar Pradesh
        'WB', // West Bengal
    ];

    public $compareIdentical = true;
}
