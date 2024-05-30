<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

use mysqli;

abstract class AbstractSpecifiersConfig implements SpecifiersConfigInterface
{
    final public function __construct(protected mysqli $mysqli)
    {
    }

    /**
     * Get Regex string to find all configurated specifiers
     *
     * @return string Regex string
     */
    final public function getRegex(): string
    {
        return '/'
            . join(
                '|',
                array_map(
                    callback: fn(SpecifierInterface $specifier): string => addcslashes($specifier->getMask(), '?'),
                    array: $this->getList()
                )
            )
            . '/';
    }
}
