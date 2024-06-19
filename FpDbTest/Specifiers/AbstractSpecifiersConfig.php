<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

use mysqli;

abstract class AbstractSpecifiersConfig implements SpecifiersConfigInterface
{
    final public function __construct(protected mysqli $mysqli)
    {
    }
}
