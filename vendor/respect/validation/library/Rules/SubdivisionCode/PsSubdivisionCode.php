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
 * Validator for Palestine subdivision code.
 *
 * ISO 3166-1 alpha-2: PS
 *
 * @link http://www.geonames.org/PS/administrative-division-palestine.html
 */
class PsSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        'DEB', // Bethlehem [conventional] / Bayt Laḩm [Arabic]
        'DEB', // Deir El Balah [conventional] /Dayr al Balaḩ[Arabic]
        'GZA', // Gaza [conventional] / Ghazzah[Arabic]
        'HBN', // Hebron [conventional] / Al Khalīl [Arabic]
        'JEM', // Jerusalem [conventional] / Al Quds [Arabic]
        'JEN', // Jenin [conventional] / Janīn [Arabic]
        'JRH', // Jericho [conventional] / Arīḩā wal Aghwār [Arabic]
        'KYS', // Khan Yunis [conventional] / Khān Yūnis[Arabic]
        'NBS', // Nablus [conventional] / Nāblus [Arabic]
        'NGZ', // North Gaza [conventional] / Shamāl Ghazzah[Arabic]
        'QQA', // Qalqiyah [conventional] / Qalqīlyah [Arabic]
        'RBH', // Ramallah and Al Birah [conventional] / Rām Allāh wal Bīrah [Arabic]
        'RFH', // Rafah [conventional] / Rafaḩ[Arabic]
        'SLT', // Salfit [conventional] / Salfīt [Arabic]
        'TBS', // Tubas [conventional] / Ţūbās [Arabic]
        'TKM', // Tulkarm [conventional] /Ţūlkarm [Arabic]
    ];

    public $compareIdentical = true;
}
