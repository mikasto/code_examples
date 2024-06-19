<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

use InvalidArgumentException;

final class ArraySpecifier extends AbstractSpecifier implements SpecifierInterface
{
    public const MASK = '?a';
    public const TYPES_ALLOWED = ['array'];

    public function getWrapped(mixed $arg): mixed
    {
        if (!count($arg)) {
            throw new InvalidArgumentException('Array is empty');
        }

        foreach ($arg as $key => $value) {
            $arg[$key] = $this->getWrappedIteration($key, $value);
        }

        return join(', ', $arg);
    }

    private function getWrappedIteration(mixed $key, mixed $value): string
    {
        if (is_array($value)) {
            throw new InvalidArgumentException("Multi including array at the key: $key");
        }

        if (is_numeric($key)) {
            return (new MixedSpecifier($this->mysqli))->getValue($value);
        }

        return $this->getIdentityAndMixedPair($key, $value);
    }

    private function getIdentityAndMixedPair($identity_arg, $mixed_arg): string
    {
        return (new IdentitySpecifier($this->mysqli))->getValue($identity_arg)
            . ' = '
            . (new MixedSpecifier($this->mysqli))->getValue($mixed_arg);
    }
}
