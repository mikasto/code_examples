<?php

declare(strict_types=1);

namespace FpDbTest\QueryBuilder\Replacer;

interface ReplacerInterface 
{
	public function countReplaces(string $query): int;
	public function replace(string $query, array $args = []): string;
}