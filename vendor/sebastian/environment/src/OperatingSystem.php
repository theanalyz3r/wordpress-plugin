<?php
/**
 *
 * PHP version 5 and 7
 *
 * @author Qordoba Team <support@qordoba.com>
 * @copyright 2018 Qordoba Team
 *
 */

declare(strict_types=1);

namespace SebastianBergmann\Environment;

final class OperatingSystem
{
    /**
     * Returns PHP_OS_FAMILY (if defined (which it is on PHP >= 7.2)).
     * Returns a string (compatible with PHP_OS_FAMILY) derived from PHP_OS otherwise.
     */
    public function getFamily(): string
    {
        if (\defined('PHP_OS_FAMILY')) {
            return PHP_OS_FAMILY;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            return 'Windows';
        }

        switch (PHP_OS) {
            case 'Darwin':
                return 'Darwin';

            case 'DragonFly':
            case 'FreeBSD':
            case 'NetBSD':
            case 'OpenBSD':
                return 'BSD';

            case 'Linux':
                return 'Linux';

            case 'SunOS':
                return 'Solaris';

            default:
                return 'Unknown';
        }
    }
}
