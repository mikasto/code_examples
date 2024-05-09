<?php

declare(strict_types=1);

namespace FpDbTest\QueryBuilder;

interface QueryBuilderInterface
{
	public function buildQuery(string $query, array $args = []): string;
}
