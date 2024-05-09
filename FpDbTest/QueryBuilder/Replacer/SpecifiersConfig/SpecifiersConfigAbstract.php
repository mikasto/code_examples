<?php

declare(strict_types=1);

namespace FpDbTest\QueryBuilder\Replacer\SpecifiersConfig;

use FpDbTest\QueryBuilder\Replacer\SpecifiersConfig\Specifier\SpecifierInterface;
use mysqli;

abstract class SpecifiersConfigAbstract implements SpecifiersConfigInterface
{
	final public function __construct(protected mysqli $mysqli)
	{
	}

	public function getRegex(): string
	{
		return '/'
			. join(
				'|',
				array_map(
					callback: fn (SpecifierInterface $specifier): string => addcslashes($specifier->getMask(), '?'),
					array: $this->getList()
				)
			)
			. '/';
	}
}
