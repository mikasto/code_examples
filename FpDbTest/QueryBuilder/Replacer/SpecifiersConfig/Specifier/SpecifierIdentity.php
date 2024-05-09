<?php

declare(strict_types=1);

namespace FpDbTest\QueryBuilder\Replacer\SpecifiersConfig\Specifier;

class SpecifierIdentity extends SpecifierAbstract
{
    public function getMask(): string
    {
        return '?#';
    }

    public static function getTypesAllowed(): array
    {
        return ['string', 'array'];
    }

    public function getWrapped(mixed $arg): mixed
    {
        if (gettype($arg) !== 'array') {
            return "`$arg`";
        }

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
            throw new \Exception("Multy inlcluding array");
        }

        if (is_numeric($key)) {
            // make single value
            return $this->getValue($value);
        }

        // make pair with key
        return $this->getValue($key) . ' = ' . (new SpecifierMixed($this->mysqli))->getValue($value);
    }
}
