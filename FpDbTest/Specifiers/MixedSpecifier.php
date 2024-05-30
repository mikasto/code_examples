<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

final class MixedSpecifier extends AbstractSpecifier
{
    public function getMask(): string
    {
        return '?';
    }
}
