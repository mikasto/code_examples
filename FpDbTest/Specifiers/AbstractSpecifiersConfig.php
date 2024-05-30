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
     * Get Regex string to find all configuration specifiers
     *
     * @return string Regex string
     */
    final public function getRegex(): string
    {
        return '/'
            . join(
                '|',
                array_map(
                    callback: function (SpecifierInterface $specifier): string {
                        return addcslashes($specifier->getMask(), '?');
                    },
                    array: $this->getList()
                )
            )
            . '/';
    }
}
