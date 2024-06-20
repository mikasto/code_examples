<?php

declare(strict_types=1);

namespace FpDbTest;

use FpDbTest\Specifiers\AllSpecifiersMap;
use FpDbTest\Specifiers\SpecifiersFactory;
use mysqli;

class Database implements DatabaseInterface
{
    public function __construct(private mysqli $mysqli)
    {
    }

    public function buildQuery(string $query, array $args = []): string
    {
        $specifiers_factory = new SpecifiersFactory($this->mysqli);
        $query_builder = new ConditionalQueryBuilder(
            query_replacer: new SpecifiersReplacer(
                mysqli: $this->mysqli,
                specifiers_map: new AllSpecifiersMap()
            ),
            arg_value_to_skip_condition_part: $this->skip(),
        );
        return $query_builder->buildQuery($query, ...$args);
    }

    public function skip()
    {
        return null; // this value is right to pass test
    }
}
