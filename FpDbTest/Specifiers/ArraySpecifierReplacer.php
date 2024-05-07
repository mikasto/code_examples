<?php

namespace FpDbTest\Specifiers;

class ArraySpecifierReplacer extends SpecifierReplacer
{
    public static function getTypesAllowed(): array
    {
        return ['array'];
    }

    public function getWrapped(mixed $arg): mixed
    {
        if (!count($arg)) {
            throw new \Exception('Array is empty');
        }

        foreach ($arg as $key => $value) {
            $arg[$key] = $this->getWrappedIteration($key, $value);
        }

        return join(', ', $arg);
    }

    private function getWrappedIteration(mixed $key, mixed $value): string
    {
        if (is_array($value)) {
            throw new \Exception("Multi including array");
        }

        if (is_numeric($key)) {
            // make single value
            return self::replace('?', $value, $this->mysqli);
        }

        // make pair with key & value
        return self::replace('?#', $key, $this->mysqli)
            . ' = '
            . self::replace('?', $value, $this->mysqli);
    }
}
