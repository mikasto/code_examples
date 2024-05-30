<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

final class ArraySpecifier extends AbstractSpecifier
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
            return (new MixedSpecifier($this->mysqli))->getValue($value);
        }

        // make pair with key & value
        return (new IdentitySpecifier($this->mysqli))->getValue($key)
            . ' = '
            . (new MixedSpecifier($this->mysqli))->getValue($value);
    }
}
