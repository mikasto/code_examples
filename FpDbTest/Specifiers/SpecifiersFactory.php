<?php

declare(strict_types=1);

namespace FpDbTest\Specifiers;

use mysqli;

final class SpecifiersFactory
{
    public function __construct(private mysqli $mysqli)
    {
    }

    public static function getSpecifierClassNameByType(string $type): string
    {
        return ucfirst($type) . 'Specifier';
    }

    public function getInstanceByType(string $specifier_type): SpecifierInterface
    {
        $specifier_class_name = self::getSpecifierClassNameByType($specifier_type);
        $method = "get$specifier_class_name";
        return $this->$method();
    }

    public function getIntegerSpecifier(): SpecifierInterface
    {
        return new IntegerSpecifier($this->mysqli);
    }

    public function getMixedSpecifier(): SpecifierInterface
    {
        return new MixedSpecifier($this->mysqli);
    }

    public function getFloatSpecifier(): SpecifierInterface
    {
        return new FloatSpecifier($this->mysqli);
    }

    public function getIdentitySpecifier(): SpecifierInterface
    {
        return new IdentitySpecifier($this->mysqli);
    }

    public function getArraySpecifier(): SpecifierInterface
    {
        return new ArraySpecifier($this->mysqli);
    }
}
