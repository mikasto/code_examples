<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

interface SpecifiersConfigInterface
{
    public function getRegex(): string;

    public function getList(): array;
}
