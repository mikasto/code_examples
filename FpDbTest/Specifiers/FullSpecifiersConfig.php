<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

final class FullSpecifiersConfig extends AbstractSpecifiersConfig
{
    public function getList(): array
    {
        return [
            new IntegerSpecifier($this->mysqli),
            new FloatSpecifier($this->mysqli),
            new ArraySpecifier($this->mysqli),
            new IdentitySpecifier($this->mysqli),
            new MixedSpecifier($this->mysqli),
        ];
    }
}
