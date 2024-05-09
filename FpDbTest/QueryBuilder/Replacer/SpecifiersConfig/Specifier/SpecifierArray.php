<?php

declare(strict_types=1);

namespace FpDbTest\QueryBuilder\Replacer\SpecifiersConfig\Specifier;

class SpecifierArray extends SpecifierAbstract
{
    public function getMask(): string
    {
        return '?a';
    }

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
            return (new SpecifierMixed($this->mysqli))->getValue($value);
        }

        // make pair with key & value
        return (new SpecifierIdentity($this->mysqli))->getValue($key)
            . ' = '
            . (new SpecifierMixed($this->mysqli))->getValue($value);
    }
}
