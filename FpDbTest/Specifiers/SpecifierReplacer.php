<?php
declare(strict_types=1);

namespace FpDbTest\Specifiers;

use InvalidArgumentException;
use mysqli;
use Throwable;

abstract class SpecifierReplacer
{
    /**
     * key => mask for a specifier
     * value => Class name of replacer
     * Order: from long to short key for regex valid work
     */
    const SPECIFIERS_MASKS = [
        '?d' => 'IntegerSpecifierReplacer',
        '?f' => 'FloatSpecifierReplacer',
        '?a' => 'ArraySpecifierReplacer',
        '?#' => 'IdentitySpecifierReplacer',
        '?' => 'MixedSpecifierReplacer',
    ];
    protected mysqli $mysqli; // need only for real escape strings by DB driver

    final public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    final public static function getSpecifiersRegex(): string
    {
        return '/'
            . join(
                '|',
                array_map(
                    fn($value): string => addcslashes($value, '?'),
                    array_keys(self::SPECIFIERS_MASKS)
                )
            )
            . '/';
    }

    final public static function replace(string $specifier, mixed $arg, mysqli $mysqli): string
    {
        try {
            $specifier_classname = self::SPECIFIERS_MASKS[$specifier];
            $specifier_replacer = new ("FpDbTest\\Specifiers\\$specifier_classname")($mysqli);
        } catch (Throwable) {
            throw new InvalidArgumentException("Specifier classname not found: '$specifier_classname'");
        }

        if (!($specifier_replacer instanceof SpecifierReplacer)) {
            throw new \Exception('Bad SpecifierReplacer class inheritance');
        }

        return $specifier_replacer->getTextByArgument($arg);
    }

    final protected function getTextByArgument(mixed $arg): string
    {
        $type = gettype($arg);

        // type check
        if (!in_array($type, static::getTypesAllowed())) {
            throw new \Exception("Invalid argument type '$type' for " . get_called_class());
        }

        // replace boolean type to integer
        if ($type === 'boolean') {
            $arg = (int)$arg;
        }

        // convert type
        $arg = $this->getConverted($arg);

        // escape
        $arg = $this->getEscaped($arg);

        // use wrappers
        $arg = $this->getWrapped($arg);

        // replace nulls
        if (gettype($arg) === 'NULL') {
            $arg = 'NULL';
        }

        return $arg;
    }

    public static function getTypesAllowed(): array
    {
        return ['string', 'integer', 'float', 'boolean', 'NULL'];
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
