<?php

declare(strict_types=1);

namespace FpDbTest;

interface QueryBuilderInterface
{
    public function buildQuery(string $query, mixed ...$args): string;
}
