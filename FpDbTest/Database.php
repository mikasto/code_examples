<?php

declare(strict_types=1);

namespace FpDbTest;

use FpDbTest\QueryBuilder\QueryBuilderConditional;
use FpDbTest\QueryBuilder\Replacer\ReplacerSpecifiers;
use FpDbTest\QueryBuilder\Replacer\SpecifiersConfig\SpecifiersConfigFull;
use mysqli;

class Database implements DatabaseInterface
{
    public function __construct(private mysqli $mysqli)
    {
    }

    public function buildQuery(string $query, array $args = []): string
    {
        $query_builder = new QueryBuilderConditional(
            query_replacer: new ReplacerSpecifiers(
                specifiers_config: new SpecifiersConfigFull(
                    mysqli: $this->mysqli
                )
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
