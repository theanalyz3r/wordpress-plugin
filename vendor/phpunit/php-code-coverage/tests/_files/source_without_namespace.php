<?php
/**
 *
 * PHP version 5 and 7
 *
 * @author Qordoba Team <support@qordoba.com>
 * @copyright 2018 Qordoba Team
 *
 */

/**
 * Represents foo.
 */
class Foo
{
}

/**
 * @param mixed $bar
 */
function &foo($bar)
{
    $baz = function () {};
    $a   = true ? true : false;
    $b   = "{$a}";
    $c   = "${b}";
}
