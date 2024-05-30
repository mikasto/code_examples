<?php

declare(strict_types=1);

namespace FpDbTest;

use FpDbTest\Specifiers\FullSpecifiersConfig;
use mysqli;

class Database implements DatabaseInterface
{
    public function __construct(private mysqli $mysqli)
    {
    }

    public function buildQuery(string $query, array $args = []): string
    {
        $query_builder = new ConditionalQueryBuilder(
            query_replacer: new SpecifiersReplacer(
                specifiers_config: new FullSpecifiersConfig(mysqli: $this->mysqli)
            ),
            skip_value: $this->skip(),
        );
        return $query_builder->buildQuery($query, $args);
    }

    public function skip()
    {
        return null; // this value is right to pass test
    }
}
