<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

use InvalidArgumentException;

final class FloatSpecifier extends AbstractSpecifier implements SpecifierInterface
{
    public const TYPES_ALLOWED = ['string', 'integer', 'float', 'boolean', 'NULL'];
    /**
     * Maximum decimals mysql & mariadb is 38 & 30. We use 30 for best way
     */
    public const DECIMALS_MAX = 30;

    public function getConverted(mixed $arg): mixed
    {
        if (settype($arg, 'float') === false) {
            throw new InvalidArgumentException("Error on change type for '$arg' to Float");
        }
        return $arg;
    }

    public function getWrapped(mixed $arg): mixed
    {
        return number_format(
            num: (float)$arg,
            decimals: min(self::DECIMALS_MAX, self::getLocaleFloatDecimalsCount((string)$arg)),
            decimal_separator: '.',
            thousands_separator: ''
        );
    }

    private static function getLocaleFloatDecimalsCount(mixed $arg): int
    {
        ['decimal_point' => $decimal_point] = localeconv();
        return (int)strpos(strrev($arg), $decimal_point);
    }
}
