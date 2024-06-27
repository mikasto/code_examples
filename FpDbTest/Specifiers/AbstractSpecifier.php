<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

use InvalidArgumentException;
use mysqli;

abstract class AbstractSpecifier implements SpecifierInterface
{
    final public function __construct(protected mysqli $mysqli)
    {
    }

    final public function getValue(mixed $arg): string
    {
        $this->validateArg($arg);
        $arg = $this->getConverted($arg);
        $arg = $this->getEscaped($arg);
        $arg = $this->getWrapped($arg);
        $arg = $this->filterBooleanToInt($arg);
        $arg = $this->filterNullToString($arg);

        return (string)$arg;
    }

    public function validateArg(mixed $arg)
    {
        $arg_type = gettype($arg);
        if (!in_array($arg_type, static::TYPES_ALLOWED)) {
            throw new InvalidArgumentException("Invalid argument type '$arg_type' for " . get_called_class());
        }
    }

    public function filterBooleanToInt(mixed $arg): mixed
    {
        if (gettype($arg) === 'boolean') {
            $arg = (int)$arg;
        }
        return $arg;
    }

    public function filterNullToString(mixed $arg): mixed
    {
        if (gettype($arg) === 'NULL') {
            $arg = 'NULL';
        }
        return $arg;
    }

    public function getConverted(mixed $arg): mixed
    {
        if (gettype($arg) === 'double') {
            return number_format($arg, 8, '.', '');
        }
        return $arg;
    }

    public function getEscaped(mixed $arg): mixed
    {
        if (gettype($arg) === 'string') {
            return $this->mysqli->escape_string($arg);
        }
        return $arg;
    }

    public function getWrapped(mixed $arg): mixed
    {
        if (gettype($arg) === 'string') {
            return "'$arg'";
        }
        return $arg;
    }
}
