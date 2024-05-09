<?php

declare(strict_types=1);

namespace FpDbTest\QueryBuilder\Replacer\SpecifiersConfig\Specifier;

interface SpecifierInterface
{
	public function getMask(): string;
	public function getValue(mixed $arg): string;
}
