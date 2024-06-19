<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

interface SpecifierInterface
{
    public const MASK = '?';
    public const TYPES_ALLOWED = [];

    public function getValue(mixed $arg): string;
}
