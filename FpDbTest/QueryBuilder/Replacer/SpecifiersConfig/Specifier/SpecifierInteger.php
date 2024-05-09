<?php

declare(strict_types=1);

namespace FpDbTest\QueryBuilder\Replacer\SpecifiersConfig\Specifier;

class SpecifierInteger extends SpecifierAbstract
{
    public function getMask(): string
    {
        return '?d';
    }

    public static function getTypesAllowed(): array
    {
        return ['string', 'integer', 'float', 'boolean', 'NULL'];
    }

    public function getConverted(mixed $arg): mixed
    {
        if (settype($arg, 'integer') === false) {
            throw new \Exception("Error on change type for '$arg' to Integer");
        }
        return $arg;
    }
}
