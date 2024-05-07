<?php

namespace FpDbTest\Specifiers;

class FloatSpecifierReplacer extends SpecifierReplacer
{
    public static function getTypesAllowed(): array
    {
        return ['string', 'integer', 'float', 'boolean', 'NULL'];
    }

    public function getConverted(mixed $arg): mixed
    {
        if (settype($arg, 'float') === false) {
            throw new \Exception("Error on change type for '$arg' to Float");
        }
        return $arg;
    }

    public function getWrapped(mixed $arg): mixed
    {
        // TODO: validate decimals for used table column OR make checking of table scheme
        return number_format($arg, 8, '.', '');
    }
}
