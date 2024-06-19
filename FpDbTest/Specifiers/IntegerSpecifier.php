<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

use InvalidArgumentException;

final class IntegerSpecifier extends AbstractSpecifier implements SpecifierInterface
{
    public const MASK = '?d';
    public const TYPES_ALLOWED = ['string', 'integer', 'float', 'boolean', 'NULL'];

    public function getConverted(mixed $arg): mixed
    {
        if (settype($arg, 'integer') === false) {
            throw new InvalidArgumentException("Error on change type for '$arg' to Integer");
        }
        return $arg;
    }
}
