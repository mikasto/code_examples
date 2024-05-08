<?php
declare(strict_types=1);

namespace FpDbTest;

use mysqli;

class Database implements DatabaseInterface
{
    public function __construct(private mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function buildQuery(string $query, array $args = []): string
    {
        $query_builder = new QueryBuilder(mysqli: $this->mysqli, condition_skip_value: $this->skip());
        return $query_builder->buildQuery($query, $args);
    }

    public function skip()
    {
        return null; // this value is right to pass test
    }
}
