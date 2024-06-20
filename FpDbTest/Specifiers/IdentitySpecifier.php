<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

use InvalidArgumentException;

final class IdentitySpecifier extends AbstractSpecifier implements SpecifierInterface
{
    public const TYPES_ALLOWED = ['string', 'array'];

    public function getWrapped(mixed $arg): string
    {
        if (gettype($arg) !== 'array') {
            $arg = (string)$arg;
            return "`$arg`";
        }

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
            throw new InvalidArgumentException("Multy inlcluding array");
        }

        if (is_numeric($key)) {
            return $this->getValue($value);
        }

        return $this->getValue($key) . ' = ' . (new MixedSpecifier($this->mysqli))->getValue($value);
    }
}
