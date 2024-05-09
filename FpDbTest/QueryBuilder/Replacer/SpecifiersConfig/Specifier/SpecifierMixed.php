<?php

declare(strict_types=1);

namespace FpDbTest\QueryBuilder\Replacer\SpecifiersConfig\Specifier;

class SpecifierMixed extends SpecifierAbstract
{
    public function getMask(): string
    {
        return '?';
    }
}
