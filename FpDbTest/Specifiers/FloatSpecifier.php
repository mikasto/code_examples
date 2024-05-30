<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

use Exception;

final class FloatSpecifier extends AbstractSpecifier implements SpecifierInterface
{
    /**
     * Maximum decimals mysql & mariadb is 38 & 30
     */
    const int DECIMALS_MAX = 30;

    public function getMask(): string
    {
        return '?f';
    }

    public static function getTypesAllowed(): array
    {
        return ['string', 'integer', 'float', 'boolean', 'NULL'];
    }

    public function getConverted(mixed $arg): mixed
    {
        if (settype($arg, 'float') === false) {
            throw new Exception("Error on change type for '$arg' to Float");
        }
        return $arg;
    }

    public function getWrapped(mixed $arg): mixed
    {
        return number_format($arg, min(static::DECIMALS_MAX, static::getFloatDecimalsCount($arg)), '.', '');
    }

    private static function getFloatDecimalsCount(mixed $arg): int
    {
        ['decimal_point' => $decimal_point] = localeconv();
        return (int)strpos(strrev($arg), $decimal_point);
    }
}
