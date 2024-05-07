<?php

namespace FpDbTest;

use FpDbTest\Specifiers\SpecifierReplacer;
use InvalidArgumentException;
use mysqli;

class QueryBuilder
{
    private mysqli $mysqli;
    private mixed $condition_skip_value;

    public function __construct(mysqli $mysqli, mixed $condition_skip_value)
    {
        $this->mysqli = $mysqli;
        $this->condition_skip_value = $condition_skip_value;
    }

    final public function buildQuery(string $query, array $args = []): string
    {
        // works only with a non-associative arrays
        $arg_cnt = 0;
        return preg_replace_callback(
            '/[^{}]+|{[^}]+}/', // cut query to parts with conditional and not
            function ($match) use ($args, &$arg_cnt) {
                $query_part = $match[0];
                return $this->buildQueryPart($query_part, $args, $arg_cnt);
            },
            $query
        );
    }

    private function buildQueryPart(string $query_part, array $args, int &$arg_cnt): string
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
            $query_part,
            array_slice($args, $arg_cnt - $specifiers_count, $specifiers_count),
        );
    }

    private function buildQueryWithSpecifiers(string $query, array $args): string
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
            SpecifierReplacer::getSpecifiersRegex(),
            function ($match) use ($args, &$arg_cnt) {
                $specifier = $match[0];
                return SpecifierReplacer::replace($specifier, $args[$arg_cnt++], $this->mysqli);
            },
            $query
        );
    }
}
