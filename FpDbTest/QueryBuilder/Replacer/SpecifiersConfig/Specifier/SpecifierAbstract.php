<?php

declare(strict_types=1);

namespace FpDbTest\QueryBuilder\Replacer\SpecifiersConfig\Specifier;

use mysqli;

abstract class SpecifierAbstract implements SpecifierInterface
{
	public function __construct(protected mysqli $mysqli)
	{
	}

	public static function getTypesAllowed(): array
	{
		return ['string', 'integer', 'float', 'boolean', 'NULL'];
	}

	final public function getValue(mixed $arg): string
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

		return (string)$arg;
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
