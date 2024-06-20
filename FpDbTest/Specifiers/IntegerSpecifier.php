<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

use InvalidArgumentException;

final class IntegerSpecifier extends AbstractSpecifier implements SpecifierInterface
{
    public const TYPES_ALLOWED = ['string', 'integer', 'float', 'boolean', 'NULL'];

    public function getConverted(mixed $arg): mixed
    {
        if (settype($arg, 'integer') === false) {
            throw new InvalidArgumentException(
                "Error on change type for '" . var_export($arg, true) . "' to Integer"
            );
        }
        return $arg;
    }
}
