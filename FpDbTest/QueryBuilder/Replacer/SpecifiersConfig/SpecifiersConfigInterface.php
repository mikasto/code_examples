<?php

declare(strict_types=1);

namespace FpDbTest\QueryBuilder\Replacer\SpecifiersConfig;

interface SpecifiersConfigInterface
{
	public function getRegex(): string;
	public function getList(): array;
}
