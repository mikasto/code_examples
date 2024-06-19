<?php

declare(strict_types=1);

namespace FpDbTest;

interface ReplacerInterface
{
    public function countQueryReplaces(string $query): int;

    public function replaceQueryArgs(string $query, array $args = []): string;
}
