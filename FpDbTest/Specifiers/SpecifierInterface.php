<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

interface SpecifierInterface
{
    public function getMask(): string;

    public function getValue(mixed $arg): string;
}
