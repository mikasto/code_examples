<?php

declare(strict_types=1);

namespace FpDbTest;

use FpDbTest\Specifiers\DefaultSpecifiersMap;
use mysqli;

final class Database implements DatabaseInterface
{
    public function __construct(private mysqli $mysqli)
    {
    }

    public function buildQuery(string $query, array $args = []): string
    {
        $specifier_replacer = new SpecifiersReplacer(
            mysqli: $this->mysqli,
            specifiers_map: new DefaultSpecifiersMap()
        );
        $query_builder = new ConditionalQueryBuilder(
            query_replacer: $specifier_replacer,
            arg_value_to_skip_condition_part: $this->skip(),
        );
        return $query_builder->buildQuery($query, ...$args);
    }

    public function skip()
    {
        return null; // this value is right to pass test
    }
}
