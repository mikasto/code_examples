<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

final class AllSpecifiersMap implements SpecifiersMapInterface
{
    public static function getMasksToTypesByRegexPriority(): array
    {
        return [
            '?d' => 'Integer',
            '?f' => 'Float',
            '?a' => 'Array',
            '?#' => 'Identity',
            '?' => 'Mixed',
        ];
    }
}
