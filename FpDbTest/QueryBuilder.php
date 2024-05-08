<?php

declare(strict_types=1);

namespace FpDbTest;

use FpDbTest\Specifiers\SpecifierReplacer;
use Generator;
use InvalidArgumentException;
use mysqli;

/**
 * Query build class
 * Uses conditional specifiers in the queries and other simple condifiers with assigned arguments
 */
class QueryBuilder
{
    public function __construct(private mysqli $mysqli, private mixed $condition_skip_value)
    {
        $this->mysqli = $mysqli;
        $this->condition_skip_value = $condition_skip_value;
    }

    final public function buildQuery(string $query, array|Generator $args = []): string
    {
        // works only with a non-associative arrays
        $arg_cnt = 0;
        return preg_replace_callback(
            pattern: '/[^{}]+|{[^}]+}/', // cut query to parts with conditional and not
            callback: function ($match) use ($args, &$arg_cnt) {
                $query_part = $match[0];
                return $this->buildQueryPart($query_part, $args, $arg_cnt);
            },
            subject: $query
        );
    }

    private function buildQueryPart(string $query_part, array|Generator $args, int &$arg_cnt): string
    {
        // count specifiers inside the part
        if (!preg_match_all(SpecifierReplacer::getSpecifiersRegex(), $query_part, $matches)) {
            // part have no specifiers
            return $query_part;
        }
        $specifiers_count = count($matches[0]);

        $arg_cnt += $specifiers_count;
        if ($arg_cnt > count($args)) {
            throw new InvalidArgumentException("Not valid arguments count " . count($args));
        }

        return $this->buildQueryWithSpecifiers(
            query: $query_part,
            args: array_slice($args, $arg_cnt - $specifiers_count, $specifiers_count),
        );
    }

    private function buildQueryWithSpecifiers(string $query, array|Generator $args): string
    {
        // filters for conditional query
        $is_conditional = str_starts_with($query, '{');
        if ($is_conditional && in_array($this->condition_skip_value, $args)) {
            return '';
        }
        if ($is_conditional) {
            $query = str_replace(['{', '}'], '', $query);
        }

        if (!count($args)) {
            return $query;
        }

        // works only with a non-associative arrays
        $arg_cnt = 0;
        return preg_replace_callback(
            pattern: SpecifierReplacer::getSpecifiersRegex(),
            callback: function ($match) use ($args, &$arg_cnt) {
                $specifier = $match[0];
                return SpecifierReplacer::replace($specifier, $args[$arg_cnt++], $this->mysqli);
            },
            subject: $query
        );
    }
}
