<?php

namespace FpDbTest\Specifiers;

interface SpecifiersMapInterface
{
    /**
     * @return string[]
     */
    public static function getMasksToTypesByRegexPriority(): array;
}
